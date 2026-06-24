<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Point;
use App\Models\Course;
use App\Models\Option;
use App\Models\Wallet;
use GuzzleHttp\Client;
use App\Helpers\Helper;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Classified;
use App\Models\AccountType;
use Illuminate\Http\Request;
use App\Models\WalletMovements;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\AccountTypePointsMoney;
use App\Http\Controllers\OptionController;
use App\Models\UnverifiedPayment;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Cache;
use App\Services\PHPMailerService;

class PayController extends Controller
{
    private $client_http;
    private $client_id;
    private $secret;

    public function __construct()
    {
        $this->client_http = new Client([
            'base_uri' => 'https://api-m.sandbox.paypal.com'
        ]);

        $this->client_id = config('services.paypal.client_id');
        $this->secret = config('services.paypal.secret');
    }

    public function viewMembershipPay()
    {
        if (session()->missing('body')) {
            return redirect()->route('login-form')->withWarning("Proceda a registrarse");
        }

        $id_membership = session()->get("body")["id_account_type"];
        $info_membership = AccountType::where('id', $id_membership)->get()->first();
        $user_info = session()->get("body");
        $price_total = $info_membership->price + ($info_membership->price * ($info_membership->iva / 100));
        $info_membership->total = sprintf('%.2f', $price_total);
        $info_membership->price = sprintf('%.2f', $info_membership->price);
        $data = array($info_membership, $user_info);
        return view('content.user-membreship.payMembership')->with('data', $data);
    }

    public function viewMembershipPayUpdate(int $membershipId)
    {
        $id_membership = $membershipId;
        $info_membership = AccountType::where('id', $id_membership)->get()->first();
        $user_info = auth()->user();
        $price_total = $info_membership->price + ($info_membership->price * ($info_membership->iva / 100));
        $info_membership->total = sprintf('%.2f', $price_total);
        $info_membership->price = sprintf('%.2f', $info_membership->price);
        $data = array($info_membership, $user_info, $membershipId);
        return view('content.user-membreship.payMembershipUpdate')->with('data', $data);
    }

    public function viewRecompra() // paypal pagar opc
    {
        $product = Product::where('name', '=', 'opc')->get()->first();
        $user_info = auth()->user();
        $igv = $product->price * 0.18;
        $total = $igv + $product->price;
        $data = array($product, $user_info, $total, $igv);
        return view('content.user-membreship.payRecompra')->with('data', $data);
    }

    public function payOPC()
    {
        $ip = request()->ip();
        $purchase_number = Helper::generatePurchaseCode();
        $product = Product::where('name', '=', 'opc')->get()->first();
        $user = auth()->user();
        $igv = $product->price * 0.18;
        $total = $igv + $product->price;
        $opc_data = compact('user', 'total', 'igv', 'product', 'ip', 'purchase_number');
        session(['opc_data' => $opc_data]);
        return view('content.opc.payment', $opc_data);
    }

    public function payWallet(Request $request)
    {
        $user = auth()->user();
        
        // 1. RATE LIMITING MEJORADO CON LOCK ATÓMICO
        $rateLimitKey = 'opc_purchase_lock_' . $user->id;
        $lockAcquired = Cache::lock($rateLimitKey, 10)->get(function () use ($user) {
            // Verificar rate limit dentro del lock
            $recentPurchases = Cache::get('opc_count_' . $user->id, 0);
            if ($recentPurchases >= 1) { // Solo 1 compra por minuto para mayor seguridad
                return false;
            }
            return true;
        });
    
        if (!$lockAcquired) {
            return response()->json([
                'error' => 'Demasiadas compras seguidas. Espera 1 minuto.',
                'limite' => '1 compra por minuto'
            ], 429);
        }
    
        try {
            // 2. TRANSACCIÓN ATÓMICA DESDE EL INICIO
            DB::beginTransaction();
        
            // 3. OBTENER INFORMACIÓN DEL PRODUCTO Y CUOTAS
            $product = Product::where('name', '=', 'opc')->first();
            if (!$product) {
                throw new \Exception('Producto OPC no encontrado');
            }
            
            $cuotas = (int) $request->input('cuotas', 1);
            if ($cuotas < 1) $cuotas = 1;
            
            $totalPrice = $product->price * $cuotas;
            $totalPoints = $product->points * $cuotas;
        
            // 4. VALIDAR QUE EL USUARIO ESTÉ ACTIVO
            if (!$user->active) {
                throw new \Exception('Tu cuenta está inactiva');
            }
        
            // 5. CRÍTICO: OBTENER WALLET CON LOCK PARA EVITAR RACE CONDITIONS
            $wallet = Wallet::where('user_id', $user->id)
                            ->lockForUpdate() // LOCK CRÍTICO
                            ->first();
            
            if (!$wallet) {
                throw new \Exception('Wallet no encontrada');
            }
        
            // 6. VERIFICAR SALDO REAL DE LA WALLET CON LOCK
            $currentBalance = app(CartController::class)->retrieveWalletBalanceUser($user->id);
            
            // 7. VALIDACIÓN CRÍTICA: VERIFICAR SALDO SUFICIENTE DENTRO DE LA TRANSACCIÓN
            if ($currentBalance < $totalPrice) {
                throw new \Exception(json_encode([
                    'type' => 'insufficient_funds',
                    'error' => 'Fondos insuficientes en la billetera',
                    'saldo_actual' => '$' . number_format($currentBalance, 2),
                    'monto_requerido' => '$' . number_format($totalPrice, 2),
                    'faltante' => '$' . number_format($totalPrice - $currentBalance, 2)
                ]));
            }
        
            // 8. DOBLE VERIFICACIÓN: VALIDAR QUE EL SALDO NO QUEDARÁ NEGATIVO
            $balanceAfterPurchase = $currentBalance - $totalPrice;
            if ($balanceAfterPurchase < 0) {
                throw new \Exception(json_encode([
                    'type' => 'negative_balance',
                    'error' => 'Esta compra dejará tu saldo en negativo',
                    'saldo_actual' => '$' . number_format($currentBalance, 2),
                    'saldo_resultante' => '$' . number_format($balanceAfterPurchase, 2)
                ]));
            }
        
            Log::info('OPC Purchase: Validaciones pasadas', [
                'user_id' => $user->id,
                'current_balance' => $currentBalance,
                'total_price' => $totalPrice,
                'cuotas' => $cuotas,
                'balance_after' => $balanceAfterPurchase
            ]);
        
            // 9. REGISTRAR EL PAGO
            $payment = new Payment();
            $payment->user_id = $user->id;
            $payment->id_user_sponsor = $user->id_referrer_sponsor;
            $payment->amount = $totalPrice;
            $payment->operation_number = 5;
            $payment->id_payment_method = 5;
            $payment->details = json_encode([
                'product_name' => $product->name,
                'cuotas' => $cuotas,
                'wallet_balance_before' => $currentBalance,
                'wallet_balance_after' => $balanceAfterPurchase,
                'purchase_timestamp' => now()->toDateTimeString(),
                'transaction_id' => uniqid('opc_', true)
            ]);
            
            if (!$payment->save()) {
                throw new \Exception('Error al registrar el pago');
            }
        
            Log::info('OPC Purchase: Pago registrado', [
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'amount' => $totalPrice
            ]);
        
            // 10. CRÍTICO: DESCONTAR DE LA BILLETERA INMEDIATAMENTE DESPUÉS DE VALIDAR
            $walletResult = $this->saveOpcWallet($user->id, $totalPrice, 1, "OPC Purchase - Payment ID: " . $payment->id . " - Cuotas: " . $cuotas);
            
            // Verificar que el descuento fue exitoso
            if (!$walletResult) {
                throw new \Exception('Error al procesar el descuento de la billetera');
            }
        
            Log::info('OPC Purchase: Descuento aplicado', [
                'user_id' => $user->id,
                'amount_deducted' => $totalPrice,
                'payment_id' => $payment->id
            ]);
        
            // 11. ACTUALIZAR FECHAS DE EXPIRACIÓN
            $userUpdate = User::where('id', $user->id)->first();
            if ($user->id_account_type == 5 || $user->id_account_type == 6) {
                $userPromotionDays = Carbon::now();
            } else {
                // SIEMPRE sumar días a la fecha existente (incluso si está vencida).
                // Esto permite pagos parciales: pagar 1 de 3 cuotas pendientes
                // solo avanza la fecha 30 días, dejando las 2 cuotas restantes.
                $userPromotionDays = Carbon::parse($userUpdate->expiration_date);
            }
            $userPromotionDays->addMonths($cuotas);
            $userUpdate->expiration_date = $userPromotionDays;
            
            if (!$userUpdate->save()) {
                throw new \Exception('Error al actualizar fecha de expiración');
            }
        
            // 12. PROCESAR PUNTOS (lógica original con validaciones mejoradas)
            $id = $user->id;
            $fullName = $user->name;
            $membersip = $user->id_account_type;
        
            $classified_user = Classified::where('user_id', $id)->first();
            if (!$classified_user) {
                throw new \Exception('Usuario no encontrado en clasificados');
            }
        
            $save_position_branch = $classified_user->position;
            $aux = false;
            $iterations = 0; // Prevenir loops infinitos
            $maxIterations = 50;
        
            if ($membersip != 5 && $membersip != 6) {
                $tmp_id = $classified_user->user_id;
                
                while ($aux == false && $iterations < $maxIterations) {
                    $user_data = Classified::where('user_id', $tmp_id)->first();
                    if (!$user_data) break;
                
                    $aux = $user_data->user_above == 'top' ? true : false;
                    $user_status = User::find($tmp_id);
                
                    if ($user_status && $user_status->active && $user_status->qualified && $user_status->membershipActive) {
                        Point::create([
                            'user_id' => $user->id,
                            'sponsor_id' => $user_data->user_id,
                            'points' => $totalPoints,
                            'side' => $save_position_branch,
                            'reason' => "OPC points, " . $fullName
                        ]);
                    } elseif ($classified_user->id_user_sponsor == $user_data->user_id) {
                        Point::create([
                            'user_id' => $user->id,
                            'sponsor_id' => $classified_user->id_user_sponsor,
                            'points' => $totalPoints,
                            'side' => $save_position_branch,
                            'reason' => "OPC points, " . $fullName
                        ]);
                    }
                    
                    $save_position_branch = $user_data->position;
                    $tmp_id = $user_data->user_above;
                    $iterations++;
                }
            }
        
            // 13. CONFIRMAR TRANSACCIÓN SOLO SI TODO SALIÓ BIEN
            DB::commit();
        
            // 14. ACTUALIZAR RATE LIMITING DESPUÉS DE COMPRA EXITOSA
            Cache::put('opc_count_' . $user->id, 1, now()->addMinute());
        
            // 15. OBTENER SALDO ACTUALIZADO PARA LA RESPUESTA
            $newBalance = app(CartController::class)->retrieveWalletBalanceUser($user->id);
        
            Log::info('OPC Purchase: Transacción completada exitosamente', [
                'user_id' => $user->id,
                'payment_id' => $payment->id,
                'amount' => $totalPrice,
                'cuotas' => $cuotas,
                'wallet_balance_before' => $currentBalance,
                'wallet_balance_after' => $newBalance,
                'iterations_points' => $iterations
            ]);
        
            return response()->json([
                'success' => true,
                'message' => "Compra OPC realizada exitosamente ($cuotas cuotas)",
                'amount_charged' => '$' . number_format($totalPrice, 2),
                'previous_balance' => '$' . number_format($currentBalance, 2),
                'new_balance' => '$' . number_format($newBalance, 2),
                'expiration_extended' => (30 * $cuotas) . ' días',
                'transaction_id' => $payment->id
            ]);
        
        } catch (\Throwable $th) {
            DB::rollBack();
        
            // Parsear errores específicos
            $errorMessage = $th->getMessage();
            $isJsonError = false;
            $errorData = null;
        
            if (strpos($errorMessage, '{') === 0) {
                $errorData = json_decode($errorMessage, true);
                $isJsonError = $errorData !== null;
            }
        
            Log::error('Error al procesar el pago por billetera OPC', [
                'user_id' => $user->id,
                'error_message' => $errorMessage,
                'error_line' => $th->getLine(),
                'error_file' => $th->getFile(),
                'is_business_logic_error' => $isJsonError,
                'trace' => $th->getTraceAsString()
            ]);
        
            // Respuesta diferenciada según el tipo de error
            if ($isJsonError && isset($errorData['type'])) {
                return response()->json($errorData, 400);
            }
        
            return response()->json([
                'error' => 'Error interno del servidor durante la compra',
                'debug_info' => app()->environment('local') ? $th->getMessage() : null
            ], 500);
        }
    }

    public function savePaymentRecharge($userID, $price, $operationNumber, $paymentId, $detailPurchase)
    {
        try {
            DB::beginTransaction();

            $payment = new Payment();
            $payment->user_id = $userID;
            $payment->id_user_sponsor = $userID;
            $payment->amount = $price;
            $payment->operation_number = $operationNumber;
            $payment->id_payment_method = $paymentId;
            $payment->details = $detailPurchase;
            $payment->save();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Ocurrio un error al realizar el registro', [$th->getMessage()]);
        }
    }

    public function saveOpcWallet($userID, $price, $reasonId, $nameCourse)
    {
        $amount = $price;
        $user = $userID;
    
        // 1. VALIDAR QUE EL USUARIO EXISTE
        $userExists = User::find($user);
        if (!$userExists) {
            Log::error('Usuario no encontrado para movimiento de billetera', ['user_id' => $user]);
            return ['status' => 'error', 'message' => 'Usuario no encontrado'];
        }
    
        // 2. VALIDAR QUE LA BILLETERA EXISTS
        $myWallet = Wallet::where('user_id', $user)->first();
        if (!$myWallet) {
            Log::error('Billetera no encontrada para usuario', ['user_id' => $user]);
            return ['status' => 'error', 'message' => 'Billetera no encontrada'];
        }
    
        // 3. VALIDAR MONTO
        if ($amount <= 0) {
            Log::error('Monto inválido para movimiento de billetera', [
                'user_id' => $user,
                'amount' => $amount
            ]);
            return ['status' => 'error', 'message' => 'Monto inválido'];
        }
    
        // 4. PARA DÉBITOS (reasonId != 4), VALIDAR SALDO DISPONIBLE
        if ($reasonId != 4) { // 4 = Recarga de Fondos (crédito)
            $currentBalance = app(CartController::class)->retrieveWalletBalanceUser($user);
            
            if ($currentBalance < $amount) {
                Log::warning('Intento de débito sin fondos suficientes', [
                    'user_id' => $user,
                    'current_balance' => $currentBalance,
                    'attempted_debit' => $amount,
                    'reason_id' => $reasonId
                ]);
                return ['status' => 'error', 'message' => 'Fondos insuficientes'];
            }
        }
    
        $last_batch = Option::lastBatch();
        $last_batch = (int) $last_batch->value;
    
        try {
            DB::beginTransaction();
            
            $movement = new WalletMovements();
            $movement->wallet_id = $myWallet->id;
            $movement->amount = $reasonId == 4 ? $amount : -$amount;
            $movement->type = $reasonId == 4 ? 1 : 0; // 1 = crédito, 0 = débito
            $movement->batch = $last_batch;
            $movement->status = 1;
        
            // Mejorar las descripciones de los movimientos
            switch ($reasonId) {
                case 1:
                    $movement->reason = 'Recompra OPC - ' . now()->format('d/m/Y H:i:s');
                    break;
                case 2:
                    $movement->reason = 'Actualización membresía - ' . now()->format('d/m/Y H:i:s');
                    break;
                case 3:
                    $movement->reason = 'Compra de curso: ' . $nameCourse . ' - ' . now()->format('d/m/Y H:i:s');
                    break;
                case 4:
                    $movement->reason = 'Recarga de Fondos - ' . now()->format('d/m/Y H:i:s');
                    break;
                default:
                    $movement->reason = 'Movimiento de billetera - ' . now()->format('d/m/Y H:i:s');
            }
        
            if ($movement->save()) {
                $response['status'] = 'ok';
                $response['movement_id'] = $movement->id;
                
                // Log del movimiento exitoso
                Log::info('Movimiento de billetera registrado', [
                    'user_id' => $user,
                    'movement_id' => $movement->id,
                    'amount' => $amount,
                    'type' => $movement->type,
                    'reason' => $movement->reason
                ]);
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Error al guardar movimiento';
            }
        
            DB::commit();
            
            return $response;
        
        } catch (\Throwable $th) {
            DB::rollBack();
            
            Log::error('Error en saveOpcWallet', [
                'user_id' => $user,
                'amount' => $amount,
                'reason_id' => $reasonId,
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            
            return ['status' => 'error', 'message' => 'Error interno del servidor'];
        }
    }

    public function payMembershipUpdate(Request $request)
    {
        try {
            DB::beginTransaction();

            $membership_id = $request->membership_id;
            $user = auth()->user();

            // =====================================================
            // VALIDACIONES DE SEGURIDAD ESENCIALES
            // =====================================================

            // 1. VALIDAR QUE LA MEMBRESÍA EXISTE
            $account_type = AccountType::find($membership_id);
            if (!$account_type) {
                return response()->json(['error' => 'Membresía no encontrada'], 404);
            }

            // 2. VALIDAR QUE NO ES LA MISMA MEMBRESÍA ACTUAL
            if ($user->id_account_type == $membership_id) {
                return response()->json(['error' => 'Ya tienes esta membresía activa'], 400);
            }

            // 3. CALCULAR COSTOS CORRECTAMENTE
            $base_price = $account_type->price;
            $iva_amount = $base_price * ($account_type->iva / 100);
            $total_amount = $base_price + $iva_amount;

            // 4. VALIDACIÓN CRÍTICA: VERIFICAR FONDOS SUFICIENTES EN BILLETERA
            $user_wallet_balance = app(CartController::class)->retrieveWalletBalanceUser($user->id);
            if ($user_wallet_balance < $total_amount) {
                return response()->json([
                    'error' => 'Fondos insuficientes en la billetera',
                    'saldo_actual' => '$' . number_format($user_wallet_balance, 2),
                    'monto_requerido' => '$' . number_format($total_amount, 2),
                    'faltante' => '$' . number_format($total_amount - $user_wallet_balance, 2)
                ], 400);
            }

            // 5. VALIDAR PARÁMETROS DE ENTRADA
            if (!is_numeric($membership_id) || $membership_id < 1) {
                return response()->json(['error' => 'ID de membresía inválido'], 400);
            }

            // 6. RATE LIMITING: Máximo una actualización cada 5 minutos por usuario
            $rateLimitKey = 'membership_update_' . $user->id;
            if (cache()->has($rateLimitKey)) {
                return response()->json([
                    'error' => 'Debes esperar 5 minutos entre actualizaciones de membresía'
                ], 429);
            }

            // =====================================================
            // PROCESAMIENTO LEGÍTIMO DE LA TRANSACCIÓN
            // =====================================================

            // Registrar el pago ANTES de actualizar (importante para auditoría)
            $payment = new Payment();
            $payment->user_id = $user->id;
            $payment->id_user_sponsor = $user->id_referrer_sponsor;
            $payment->amount = $total_amount;
            $payment->operation_number = 5;
            $payment->id_payment_method = 5;
            $payment->details = json_encode([
                'previous_membership_id' => $user->id_account_type,
                'previous_membership_name' => AccountType::find($user->id_account_type)->name ?? 'Unknown',
                'new_membership_id' => $membership_id,
                'new_membership_name' => $account_type->name,
                'base_price' => $base_price,
                'iva_percentage' => $account_type->iva,
                'iva_amount' => $iva_amount,
                'total_charged' => $total_amount,
                'wallet_balance_before' => $user_wallet_balance,
                'upgrade_timestamp' => now()->toDateTimeString()
            ]);
            $payment->save();

            // Calcular fechas de renovación
            if ($user->id_account_type == 5 || $user->id_account_type == 6) {
                $userRenewMembership = Carbon::now();
                $userRenewOPC = Carbon::now();
            } else {
                $userRenewMembership = Carbon::createFromFormat('Y-m-d H:i:s', $user->expiration_membership_date);
                $userRenewOPC = Carbon::createFromFormat('Y-m-d H:i:s', $user->expiration_date);
            }
            $userRenewMembership->addDays(365);
            $userRenewOPC->addDays(30);

            // Actualizar usuario con la nueva membresía
            $userUpdate = User::find($user->id);
            $previous_membership = $userUpdate->id_account_type; // Guardar para el log
            $userUpdate->id_account_type = $membership_id;
            $userUpdate->expiration_membership_date = $userRenewMembership;
            $userUpdate->expiration_date = $userRenewOPC;
            $userUpdate->save();

            // MANTENER LÓGICA ORIGINAL DE PUNTOS Y BONOS (sin cambios)
            $id = $user->id;
            $fullName = $user->name;
            $membersip = $user->id_account_type;

            $atm = AccountTypePointsMoney::where('account_type_id', $membership_id)->first();
            $classified_user = Classified::where('user_id', $id)->first();
            $save_position_branch = $classified_user->position;

            $aux = false;

            if ($membership_id != 5 && $membership_id != 6) {
                $tmp_id = $classified_user->user_id;
                while ($aux == false) {
                    $user_data = Classified::where('user_id', $tmp_id)->first();
                    $aux = $user_data->user_above == 'top' ? true : false;
                    $user_status = User::find($tmp_id);
                    if ($user_status->active && $user_status->qualified && $user_status->membershipActive) {
                        Point::create([
                            'user_id' => $user->id,
                            'sponsor_id' => $user_data->user_id,
                            'points' => $atm->points,
                            'side' => $save_position_branch,
                            'reason' => "Membership buy, " . $fullName
                        ]);
                    } elseif ($classified_user->id_user_sponsor == $user_data->user_id) {
                        Point::create([
                            'user_id' => $user->id,
                            'sponsor_id' => $classified_user->id_user_sponsor,
                            'points' => $atm->points,
                            'side' => $save_position_branch,
                            'reason' => "Membership buy, " . $fullName
                        ]);
                    }
                    $save_position_branch = $user_data->position;
                    $tmp_id = $user_data->user_above;
                }

                $last_batch = Option::lastBatch();
                $last_batch = (int) $last_batch->value;

                if ($membership_id != 5 && $membership_id != 6) {
                    if ($user->id_referrer_sponsor != 1) {
                        $id_account_type_sponsor = User::select('id_account_type')->where('id', $user->id_referrer_sponsor)->first();
                        $fast_cash_sponsor = AccountType::select('fast_cash_bonus')->where('id', $id_account_type_sponsor->id_account_type)->first();
                        $walletParentDirect = Wallet::where('user_id', $user->id_referrer_sponsor)->first();
                        $movement = new WalletMovements();
                        $movement->wallet_id = $walletParentDirect->id;
                        $movement->amount = $base_price * ($fast_cash_sponsor->fast_cash_bonus / 100);
                        $movement->type = 1;
                        $movement->batch = $last_batch;
                        $movement->bonus_type_id = 1;
                        $movement->reason = 'Bono de efectivo rápido de ' . $user->username;
                        $movement->save();
                    }
                }
            }

            // Actualizar fecha de expiración usando el controlador original
            $userController = new UserController();
            $userController->updateUserMembershipExpirationDate($user->id, $account_type->id);

            // CRÍTICO: DESCONTAR EL MONTO DE LA BILLETERA
            $this->saveOpcWallet($user->id, $total_amount, 2, "");

            // 🆕 ENVIAR COMPROBANTE DE PAGO DE MEMBRESÍA
            $this->sendMembershipReceipt(
                $user->id,
                $membership_id,
                $total_amount,
                $base_price,
                $iva_amount,
                $account_type->iva,
                'Billetera Promolíder',
                $payment->id
            );

            // Establecer rate limit después del éxito
            cache()->put($rateLimitKey, true, now()->addMinutes(5));

            DB::commit();

            // Log de transacción exitosa (para auditoría)
            Log::info('Actualización de membresía procesada correctamente', [
                'user_id' => $user->id,
                'username' => $user->username,
                'previous_membership' => $previous_membership,
                'new_membership' => $membership_id,
                'amount_charged' => $total_amount,
                'wallet_balance_before' => $user_wallet_balance,
                'wallet_balance_after' => $user_wallet_balance - $total_amount,
                'payment_id' => $payment->id,
                'timestamp' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Membresía actualizada correctamente',
                'membership_name' => $account_type->name,
                'amount_charged' => '$' . number_format($total_amount, 2),
                'new_balance' => '$' . number_format($user_wallet_balance - $total_amount, 2)
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error('Error al procesar actualización de membresía', [
                'user_id' => auth()->id(),
                'membership_id' => $request->membership_id ?? null,
                'error_message' => $th->getMessage(),
                'error_line' => $th->getLine(),
                'error_file' => $th->getFile(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'error' => 'Error interno del servidor',
                'debug_info' => app()->environment('local') ? $th->getMessage() : null
            ], 500);
        }
    }

    public function openpayOPC(Request $request)
    {
        $user = auth()->user();
    
        // ID de la membresía del usuario
        $accountTypeId = $user->accountType->id ?? null;
    
        // Buscar el producto "opc" que coincida con la membresía del usuario
        $product = Product::where('name', 'opc')
            ->where('account_type_id', $accountTypeId)
            ->first();
    
        if (!$product) {
            return back()->withErrors(['msg' => 'No existe un producto OPC asociado a esta membresía']);
        }
    
        // Leer cuotas y multiplicar precio
        $cuotas = (int) $request->query('cuotas', 1);
        if ($cuotas < 1) $cuotas = 1;

        $user_id = $user->id;
        $user_name = $user->name;
        $user_lastname = $user->last_name;
        $user_phone = $user->phone;
        $user_email = $user->email;
        $product_id = $product->id;
        $product_name = $product->name;
        $now = Carbon::now();
        $product_price = $product->price * $cuotas; // precio base multiplicado por cuotas
        $product_detail = "Recompra de OPC - " . $cuotas . " cuota(s)";
        $key_openpay = config('services.openpay.sk_encoded');
        $id_openpay = config('services.openpay.id');
    
        return view('content.opc.openpay', compact(
            'product_id',
            'product_name',
            'user_id',
            'product_price',
            'product_detail',
            'user_name',
            'user_lastname',
            'user_phone',
            'user_email',
            'key_openpay',
            'id_openpay'
        ));
    }

    public function authorizeopc(Request $request)
    {
        $transaction_token = $request->transactionToken;

        $merchant_id = '456879853';
        $api = "https://apisandbox.vnforappstest.com/api.authorization/v3/authorization/ecommerce/$merchant_id}";

        $data = session()->get("opc_data");

        $purchase_number = session()->get("opc_data")["purchase_number"];
        $total = session()->get("opc_data")["total"];

        $purchase_data = array(
            "channel" => $request->channel,
            "captureType" => "manual",
            'countable' => 'true',
            "order" => array(
                "tokenId" => $transaction_token,
                "purchaseNumber" => $purchase_number,
                "amount" => $total,
                "currency" => "USD"
            ),
        );

        $access_token = NiubizController::createNiubizToken();

        return view('content.opc.invoice', compact('purchase_data', 'access_token', 'data', 'purchase_number'));
    }

    public function opcprocess(Request $request)
    {
        return $this->registerSuccessRecompra($request['order']['purchaseNumber']);
    }

    public function getAccessTokenPaypal()
    {
        $response = $this->client_http->request('POST', '/v1/oauth2/token', [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => 'grant_type=client_credentials',
            'auth' => [$this->client_id, $this->secret, 'basic']
        ]);

        $data = json_decode($response->getBody(), true);
        return $data["access_token"];
    }

    public function registerSuccessPayment($order_id)
    {
        Log::info('🔥 registerSuccessPayment EJECUTADO', [
            'order_id' => $order_id
        ]);
        app(UserController::class)->Create($order_id);
    }

    public function registerSuccessPaymentUpdate($order_id, $membership_id)
    {
        app(UserController::class)->membershipUpdate($order_id, $membership_id);
    }

    public function registerSuccessRecompra($order_id)
    {
        app(UserController::class)->recompraUpdate($order_id);
    }

    public function process($order_id)
    {
        $access_token = $this->getAccessTokenPaypal();
        $response = $this->client_http->request('GET', '/v2/checkout/orders/' . $order_id, [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => "Bearer $access_token",
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        if ($data["status"] == 'APPROVED') {
            $this->registerSuccessPayment($order_id);
            return [
                'success' => true,
                'data' => $data
            ];
        }
        return [
            'error' => false
        ];
    }

    public function processUpdate($order_id, $membership_id)
    {
        $access_token = $this->getAccessTokenPaypal();
        $response = $this->client_http->request('GET', '/v2/checkout/orders/' . $order_id, [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => "Bearer $access_token",
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        if ($data["status"] == 'APPROVED') {
            $this->registerSuccessPaymentUpdate($order_id, $membership_id);
            return [
                'success' => true,
                'data' => $data
            ];
        }
        return [
            'error' => false
        ];
    }

    public function processRecompra($order_id)
    {
        $access_token = $this->getAccessTokenPaypal();
        $response = $this->client_http->request('GET', '/v2/checkout/orders/' . $order_id, [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => "Bearer $access_token",
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        if ($data["status"] == 'APPROVED') {
            $this->registerSuccessRecompra($order_id);
            return [
                'success' => true,
                'data' => $data
            ];
        }
        return [
            'error' => false
        ];
    }

    public function openpayMembership($id)
    {
        $user = auth()->user();
        $account = AccountType::where('id', $id)
            ->select('price', 'account', 'iva')
            ->first();

        $base_price = $account->price;
        $iva_amount = $base_price * ($account->iva / 100);
        $total_price = $base_price + $iva_amount;

        $user_id = $user->id;
        $user_name = $user->name;
        $user_lastname = $user->last_name;
        $user_phone = $user->phone;
        $user_email = $user->email;
        $product_id = $id;
        $product_name = "membership";
        $product_price = $total_price;
        $product_detail = "Recompra de membresía " . $account->account;
        $key_openpay = config('services.openpay.sk_encoded');
        $id_openpay = config('services.openpay.id');

        return view('content.opc.openpay', compact('product_id', 'product_name', 'user_id', 'product_price', 'product_detail', 'user_name', 'user_lastname', 'user_phone', 'user_email', 'key_openpay', 'id_openpay'));
    }

    public function openpayRecharge($mount, $typepayment)
    {
        try {
            $user = auth()->user();
            
            // Verificar que el usuario esté autenticado
            if (!$user) {
                return redirect()->route('login')->with('error', 'Debe iniciar sesión');
            }
        
            // Validar parámetros
            if (!is_numeric($mount) || $mount <= 0) {
                return back()->with('error', 'Monto inválido');
            }
        
            // Verificar credenciales de Openpay
            $key_openpay = config('services.openpay.sk_encoded');
            $id_openpay = config('services.openpay.id');
            
            if (empty($key_openpay) || empty($id_openpay)) {
                Log::error('Credenciales de Openpay no configuradas', [
                    'key_configured' => !empty($key_openpay),
                    'id_configured' => !empty($id_openpay)
                ]);
                return back()->with('error', 'Sistema de pagos no configurado correctamente');
            }
        
            $product_id = $typepayment;
            $product_name = "recharge_found";
            $user_id = $user->id;
            $product_price = $mount;
            $product_detail = "Recargar Fondos Billetera";
            $user_name = $user->name;
            $user_lastname = $user->last_name ?? '';
            $user_phone = $user->phone ?? '';
            $user_email = $user->email;
        
            Log::info('Cargando vista de Openpay', [
                'user_id' => $user_id,
                'amount' => $product_price,
                'payment_type' => $product_id
            ]);
        
            return view('content.opc.openpay', compact(
                'product_id', 
                'product_name', 
                'user_id', 
                'product_price', 
                'product_detail', 
                'user_name', 
                'user_lastname', 
                'user_phone', 
                'user_email', 
                'key_openpay', 
                'id_openpay'
            ));
        
        } catch (\Exception $e) {
            Log::error('Error en openpayRecharge', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->with('error', 'Error interno del servidor');
        }
    }

    public function rechargeOpenpayProcess(Request $request)
    {
        $openpay_id = config('services.openpay.id');
        $openpay_sk = config('services.openpay.sk');
        $app_url = env("APP_URL");
    
        Log::info('Configurando Openpay', [
            'openpay_id' => $openpay_id,
            'app_url' => $app_url
        ]);
    
        $openpay = \Openpay\Data\Openpay::getInstance($openpay_id, $openpay_sk, 'PE', $request->ip());
        \Openpay\Data\Openpay::setProductionMode(false);

        $customer = $request->customer;
    
        // 🔹 Genera el order_id y asegura longitud máxima de 100 caracteres
        $orderResponse = app(OptionController::class)->openpayOrder();
        $orderData = json_decode($orderResponse->getContent(), true);
        $order_id = substr('promolider2024-' . $orderData['data']['openpay_order'], 0, 100);
    
        $chargeRequest = [
            'order_id'     => $order_id,
            'method'       => 'card',
            'currency'     => 'USD',
            'amount'       => strval($request->amount),
            'description'  => $request->description,
            'customer'     => $customer,
            'send_email'   => false,
            'confirm'      => false,
            'redirect_url' => route('dashboard-ecommerce') . '?payment=success'
        ];
    
        Log::info('Iniciando cargo Openpay', [
            'order_id' => $order_id,
        ]);
    
        // Ejecuta la creación del cargo
        $charge = $openpay->charges->create($chargeRequest);
    
        $charge_data = [
            'payment_url' => $charge->payment_method->url,
            'charge_id'   => $charge->id
        ];
    
        return response()->json($charge_data);
    }

    public function registerOpenpayProcess(Request $request)
    {
        try {
            Log::info('Iniciando proceso de pago con Openpay', [
                'request_data' => $request->all()
            ]);

            // --- INICIO DE LA VALIDACIÓN ---
            $requestedAmount = (float) $request->amount;

            // 1. Obtener los precios base (mayores que 0) desde el modelo AccountType.
            $basePrices = AccountType::where('price', '>', 0)->pluck('price');

            // 2. Calcular el precio con el 18% de IVA para cada precio base.
            // Se usa map() para transformar cada precio y round() para evitar problemas de precisión con decimales.
            $pricesWithIVA = $basePrices->map(function ($price) {
                return round((float)$price * 1.18, 2);
            })->toArray();

            // 3. Verificar si el monto solicitado (también redondeado) se encuentra en el array de precios con IVA.
            if (!in_array(round($requestedAmount, 2), $pricesWithIVA)) {
                Log::warning('Intento de pago con monto inválido.', [
                    'requested_amount' => $requestedAmount,
                    'valid_prices_with_iva' => $pricesWithIVA,
                    'ip' => $request->ip()
                ]);

                // 4. Si el monto no es válido, retornar una respuesta de error.
                return response()->json([
                    'error' => 'El monto proporcionado no es válido.',
                    'details' => 'El monto debe corresponder a un plan de cuenta existente y válido, incluyendo el 18% de IVA.'
                ], 400); // 400 Bad Request es apropiado para una entrada de cliente incorrecta.
            }
            // --- FIN DE LA VALIDACIÓN ---

            $openpay_id = config('services.openpay.id');
            $openpay_sk = config('services.openpay.sk');
            $app_url = env("APP_URL");

            Log::info('Configurando Openpay', [
                'openpay_id' => $openpay_id,
                'app_url' => $app_url
            ]);

            $openpay = \Openpay\Data\Openpay::getInstance($openpay_id, $openpay_sk, 'PE', $request->ip());
            \Openpay\Data\Openpay::setProductionMode(false);

            $customer = $request->customer;
            $orderNumber = app(OptionController::class)->generateOrderNumber();
            $order_id = 'PROM-' . $orderNumber;

            // ✅ Validar longitud
            if (strlen($order_id) > 100) {
                throw new \Exception('Order ID excede 100 caracteres');
            }
            
            Log::info('Order ID generado', [
                'order_id' => $order_id,
                'order_number' => $orderNumber,
                'length' => strlen($order_id)
            ]);

            Log::info('Datos del cliente y orden generados', [
                'order_id' => $order_id,
                'customer' => $customer
            ]);

            $chargeRequest = [
                'order_id' => $order_id,
                'method' => 'card',
                'currency' => 'USD',
                'amount' => strval($request->amount), // Openpay espera un string para el monto
                'description' => $request->description,
                'customer' => $customer,
                'send_email' => false,
                'confirm' => false,
                'redirect_url' => $app_url . '?payment=success'
            ];

            Log::info('Request de cargo preparado', [
                'chargeRequest' => $chargeRequest
            ]);

            $charge = $openpay->charges->create($chargeRequest);

            Log::info('Cargo creado exitosamente', [
                'charge_id' => $charge->id,
                'payment_url' => $charge->payment_method->url
            ]);

            $charge_data = [
                'payment_url' => $charge->payment_method->url,
                'charge_id' => $charge->id
            ];

            return response()->json($charge_data);

        } catch (\Exception $e) {
            Log::error('Error al registrar proceso Openpay', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'No se pudo completar el proceso de pago.',
                'details' => $e->getMessage()
            ], 500);
        }
    }
    
    public function openpayCourse(Request $request)
    {
        $user = auth()->user();
        $course = Course::where('id', $request->course_id)->first();
        $user_id = $user->id;
        $user_name = $user->name;
        $user_lastname = $user->last_name;
        $user_phone = $user->phone;
        $user_email = $user->email;
        $product_id = $request->course_id;
        $product_name = "course";

        $account_type = AccountType::find(auth()->user()->id_account_type);
        $price_with_discount = round($course->price - (($course->price * $account_type->disc_purchases_course) / 100), 2);
        $product_price = $price_with_discount;
        $product_detail = "Compra de curso: " . $course->title;
        $key_openpay = config('services.openpay.sk');
        $id_openpay = config('services.openpay.id');

        $openpay = \Openpay\Data\Openpay::getInstance($id_openpay, $key_openpay, 'PE', $request->ip());
        \Openpay\Data\Openpay::setProductionMode(false);
        $customer = array(
            'name' => $user_name,
            'last_name' => $user_lastname,
            'phone_number' => $user_phone,
            'email' => $user_email
        );
        $response = app(OptionController::class)->openpayOrder();
        $order_number = $response->getData()->data->openpay_order ?? null;
        $order_id = 'promolider2024-' . $order_number;
        $chargeRequest = array(
            'order_id' => $order_id,
            'method' => 'card',
            'currency' => 'USD',
            'amount' => strval($product_price),
            'description' => $product_detail,
            'customer' => $customer,
            'send_email' => false,
            'confirm' => false,
            'redirect_url' => env('FRONTEND_APP_URL') . 'suscription-user'
        );

        $charge = $openpay->charges->create($chargeRequest);
        $charge_data = [
            'payment_url' => $charge->payment_method->url
        ];

        $payment = new UnverifiedPayment();
        $payment->user_id = $user_id;
        $payment->openpay_order_id = $charge->id;
        $payment->product_id = $product_id;
        $payment->product_detail = $product_detail;
        $payment->product_price = $product_price;
        $payment->product_name = $product_name;
        $payment->save();
        return response()->json($charge_data);
    }

    /**
     * Enviar comprobante de pago de membresía por email
     * 
     * @param int $userId
     * @param int $membershipId
     * @param float $totalPaid
     * @param float $basePrice
     * @param float $ivaAmount
     * @param float $ivaPercentage
     * @param string $paymentMethod
     * @param int $paymentId
     */
    private function sendMembershipReceipt(
        $userId, 
        $membershipId, 
        $totalPaid, 
        $basePrice, 
        $ivaAmount, 
        $ivaPercentage, 
        $paymentMethod = 'Billetera Promolíder',
        $paymentId = null
    ) {
        try {
            $user = User::find($userId);
            $membership = AccountType::find($membershipId);
            
            if (!$user || !$membership) {
                Log::warning('No se pudo enviar comprobante de membresía: Usuario o membresía no encontrado', [
                    'user_id' => $userId,
                    'membership_id' => $membershipId
                ]);
                return;
            }

            // 🆕 GENERAR NÚMERO DE COMPROBANTE CORRELATIVO
            $receiptNumber = $this->generateReceiptNumber();
            
            // Calcular fechas
            $activationDate = now();
            $expirationDate = Carbon::parse($user->expiration_membership_date);
            
            // Calcular duración
            $daysRemaining = $activationDate->diffInDays($expirationDate);
            $membershipDuration = $daysRemaining > 0 ? "{$daysRemaining} días" : "365 días";
            
            // Obtener beneficios de la membresía
            $membershipBenefits = $this->getMembershipBenefits($membership);
            
            // Nombre de la membresía (usar 'account' en lugar de 'name')
            $membershipName = $membership->account ?? 'Membresía';
            
            // Guardar comprobante en la base de datos
            DB::table('payment_receipts')->insert([
                'receipt_number' => $receiptNumber,
                'receipt_type' => 'membership',
                'user_id' => $userId,
                'course_id' => null,
                'account_type_id' => $membershipId,
                'membership_name' => $membershipName,
                'membership_duration' => $membershipDuration,
                'activation_date' => $activationDate->format('Y-m-d'),
                'expiration_date' => $expirationDate->format('Y-m-d'),
                'membership_benefits' => $membershipBenefits,
                'purchased_course_id' => null,
                'amount_paid' => $totalPaid,
                'course_price' => $basePrice,
                'discount_amount' => 0,
                'discount_percentage' => 0,
                'payment_method' => $paymentMethod,
                'payment_reference' => $paymentId,
                'email_sent_to' => $user->email,
                'email_sent_at' => now(),
                'email_status' => 'sent',
                'course_title' => null,
                'instructor_name' => null,
                'user_name' => $user->name . ' ' . $user->last_name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Preparar datos para la plantilla
            $templateData = [
                'receipt_number' => $receiptNumber,
                'user_name' => $user->name . ' ' . $user->last_name,
                'user_email' => $user->email,
                'payment_date' => $activationDate->format('d/m/Y H:i:s'),
                'payment_method' => $paymentMethod,
                'membership_name' => $membershipName,
                'membership_duration' => $membershipDuration,
                'activation_date' => $activationDate->format('d/m/Y'),
                'expiration_date' => $expirationDate->format('d/m/Y'),
                'base_price' => $basePrice,
                'iva_percentage' => $ivaPercentage,
                'iva_amount' => $ivaAmount,
                'total_paid' => $totalPaid,
                'membership_benefits' => $membershipBenefits,
                'dashboard_url' => 'https://vcr.promolider.info/account'
            ];

            // Enviar email
            $phpMailerService = new PHPMailerService();
            $phpMailerService->sendEmailWithTemplate(
                $user->email,
                'Comprobante de Pago - Membresía ' . $membershipName,
                'emails.comprobante-pago-membresia',
                $templateData
            );

            Log::info('Comprobante de pago de membresía enviado exitosamente', [
                'user_id' => $userId,
                'membership_id' => $membershipId,
                'email' => $user->email,
                'amount' => $totalPaid,
                'receipt_number' => $receiptNumber,
                'payment_method' => $paymentMethod
            ]);

        } catch (\Exception $e) {
            Log::error('Error enviando comprobante de membresía: ' . $e->getMessage(), [
                'user_id' => $userId,
                'membership_id' => $membershipId,
                'trace' => $e->getTraceAsString()
            ]);
            // No lanzar excepción para no interrumpir el flujo de compra
        }
    }

    /**
     * Obtener beneficios de la membresía en formato HTML
     */
    private function getMembershipBenefits($membership)
    {
        $benefits = [];
        
        if ($membership->disc_purchases_course > 0) {
            $benefits[] = "• {$membership->disc_purchases_course}% de descuento en cursos";
        }
        
        if ($membership->disc_purchases_product > 0) {
            $benefits[] = "• {$membership->disc_purchases_product}% de descuento en productos";
        }
        
        if ($membership->fast_cash_bonus > 0) {
            $benefits[] = "• {$membership->fast_cash_bonus}% en bono de efectivo rápido";
        }
        
        if ($membership->rank_binary_bonus > 0) {
            $benefits[] = "• {$membership->rank_binary_bonus}% en bono binario";
        }
        
        if ($membership->development_bonus > 0) {
            $benefits[] = "• {$membership->development_bonus}% en bono de desarrollo";
        }
        
        // Beneficios generales
        $benefits[] = "• Acceso a todas las funcionalidades de la plataforma";
        $benefits[] = "• Soporte prioritario";
        
        return implode('<br>', $benefits);
    }

    /**
     * Genera el número de comprobante correlativo de 5 dígitos
     * Compartido entre cursos y membresías: 00100, 00101, 00102, etc.
     */
    private function generateReceiptNumber()
    {
        // Obtener el último número de comprobante (de cursos O membresías)
        $lastReceipt = DB::table('payment_receipts')
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastReceipt) {
            // Incrementar el último número
            $lastNumber = intval($lastReceipt->receipt_number);
            $newNumber = $lastNumber + 1;
        } else {
            // Primer comprobante
            $newNumber = 100;
        }
        
        // Formatear a 5 dígitos con ceros a la izquierda
        return str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }
}
