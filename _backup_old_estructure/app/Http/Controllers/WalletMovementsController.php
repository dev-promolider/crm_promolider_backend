<?php

namespace App\Http\Controllers;

use App\Models\Option;
use Illuminate\Http\Request;
use App\Models\Wallet;
use App\Models\Notifications;
use App\Models\User;
use App\Models\WalletMovements;
use App\Http\Controllers\Api\CartController;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Helpers\Helper;

class WalletMovementsController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:report-wallets');
    }

    public function getAllMovementsWallet($user_id)
    {
        Log::info('WalletMovements: Iniciando getAllMovementsWallet', [
            'requested_user_id' => $user_id,
            'authenticated_user' => auth()->id()
        ]);

        try {
            $authenticatedUser = auth()->user();

            // 🔒 VALIDACIÓN CRÍTICA: Solo permitir ver los propios movimientos
            if ($authenticatedUser->id != $user_id) {
                Log::warning('WalletMovements: Intento de acceso no autorizado', [
                    'authenticated_user' => $authenticatedUser->id,
                    'requested_user_id' => $user_id,
                    'ip' => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]);

                return response()->json([
                    'error' => 'No tienes permisos para ver los movimientos de este usuario'
                ], 403);
            }

            $myWallet = Wallet::where('user_id', $user_id)->first();

            if (!$myWallet) {
                Log::warning('WalletMovements: Wallet no encontrado', [
                    'user_id' => $user_id
                ]);
                return response()->json(['error' => 'Wallet not found'], 404);
            }

            Log::info('WalletMovements: Wallet encontrado', [
                'wallet_id' => $myWallet->id,
                'user_id' => $user_id
            ]);

            // CORECCIÓN: Solo obtener movimientos del wallet específico del usuario
            $myMovements = WalletMovements::where('wallet_id', $myWallet->id)->get();

            Log::info('WalletMovements: Movimientos obtenidos exitosamente', [
                'wallet_id' => $myWallet->id,
                'movements_count' => $myMovements->count(),
                'authenticated_user' => $authenticatedUser->id,
                'movements_ids' => $myMovements->pluck('id')->toArray() // Para debug
            ]);

            return JsonResource::collection($myMovements);

        } catch (\Exception $e) {
            Log::error('WalletMovements: Error en getAllMovementsWallet', [
                'user_id' => $user_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Obtiene el balance total de la wallet del usuario autenticado
     * Suma todos los amounts (positivos y negativos) de los movimientos
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getWalletBalance()
    {
        $user = auth()->user();

        Log::info('WalletMovements: Iniciando obtención de balance de wallet', [
            'authenticated_user' => $user->id,
            'username' => $user->username
        ]);

        try {
            // 1. Verificar que la wallet del usuario existe
            $wallet = Wallet::where('user_id', $user->id)->first();

            if (!$wallet) {
                Log::warning('WalletMovements: Wallet no encontrada para el usuario', [
                    'user_id' => $user->id
                ]);
                return response()->json([
                    'error' => 'Wallet not found for user'
                ], 404);
            }

            Log::info('WalletMovements: Wallet encontrada', [
                'wallet_id' => $wallet->id,
                'user_id' => $user->id
            ]);

            // 2. CORREGIDO: Solo calcular balance con movimientos aprobados (status = 1)
            $totalBalance = WalletMovements::where('wallet_id', $wallet->id)
                ->where('status', 1) // Solo movimientos aprobados
                ->sum('amount'); // Suma directa de la columna amount (positivos y negativos)

            // 3. Obtener información adicional para logs y breakdown
            $movementsCount = WalletMovements::where('wallet_id', $wallet->id)
                ->where('status', 1) // Solo contar los aprobados para consistencia
                ->count();

            // Mantener información de pendientes para el breakdown (pero no incluir en balance total)
            $pendingAmount = WalletMovements::where('wallet_id', $wallet->id)
                ->where('status', 0)
                ->sum('amount');

            $approvedAmount = WalletMovements::where('wallet_id', $wallet->id)
                ->where('status', 1)
                ->sum('amount');

            // Verificación: totalBalance debe ser igual a approvedAmount
            if ($totalBalance !== $approvedAmount) {
                Log::warning('WalletMovements: Inconsistencia en cálculos de balance', [
                    'total_balance' => $totalBalance,
                    'approved_amount' => $approvedAmount,
                    'wallet_id' => $wallet->id
                ]);
            }

            Log::info('WalletMovements: Balance calculado exitosamente (solo status = 1)', [
                'wallet_id' => $wallet->id,
                'user_id' => $user->id,
                'total_balance' => $totalBalance,
                'movements_count' => $movementsCount,
                'pending_amount' => $pendingAmount,
                'approved_amount' => $approvedAmount
            ]);

            return response()->json([
                'data' => [
                    'user_id' => $user->id,
                    'wallet_id' => $wallet->id,
                    'total_balance' => $totalBalance, // Solo movimientos con status = 1
                    'formatted_balance' => '$' . number_format($totalBalance, 2),
                    'movements_count' => $movementsCount, // Solo conteo de aprobados
                    'breakdown' => [
                        'approved_amount' => $approvedAmount, // Debería ser igual a total_balance
                        'pending_amount' => $pendingAmount,   // Para información, pero no incluido en balance
                        'formatted_approved' => '$' . number_format($approvedAmount, 2),
                        'formatted_pending' => '$' . number_format($pendingAmount, 2)
                    ]
                ],
                'message' => 'Balance obtenido exitosamente (solo movimientos aprobados)'
            ], 200);

        } catch (\Exception $e) {
            Log::error('WalletMovements: Error al obtener balance de wallet', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Error interno del servidor al obtener balance'
            ], 500);
        }
    }

    public function getAllMovementsHistoryWallet()
    {
        Log::info('WalletMovements: Iniciando getAllMovementsHistoryWallet', [
            'authenticated_user' => auth()->id()
        ]);

        try {
            $allWallets = WalletMovements::where('status', 1)
                                       ->select('created_at', 'amount', 'reason', 'type')
                                       ->get();

            Log::info('WalletMovements: Historial de movimientos obtenido exitosamente', [
                'movements_count' => $allWallets->count(),
                'authenticated_user' => auth()->id()
            ]);

            return $allWallets;

        } catch (\Exception $e) {
            Log::error('WalletMovements: Error en getAllMovementsHistoryWallet', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'authenticated_user' => auth()->id()
            ]);
            
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    public function transferFounds(Request $request)
    {
        // 1. VALIDACIÓN DE ENTRADA
        $validated = $request->validate([
            'direct' => 'required|integer|exists:users,id',
            'amount' => 'required|numeric|min:0.01|max:999999.99',
        ]);

        $receiverId = $validated['direct'];
        $amount = (float) $validated['amount'];
        $user = auth()->user();

        Log::info('WalletMovements: Iniciando transferencia de fondos', [
            'sender_id' => $user->id,
            'sender_username' => $user->username,
            'receiver_id' => $receiverId,
            'amount' => $amount
        ]);

        try {
            // 2. VERIFICAR QUE EL RECEPTOR ES UN DIRECTO DEL USUARIO
            $userDirects = User::where('id_referrer_sponsor', $user->id)->pluck('id')->toArray();

            if (!in_array($receiverId, $userDirects)) {
                Log::error('WalletMovements: Intento de transferencia a usuario no autorizado', [
                    'sender_id' => $user->id,
                    'attempted_receiver_id' => $receiverId
                ]);
                return response()->json([
                    'error' => 'Solo puedes transferir fondos a tus referidos directos'
                ], 403);
            }

            Log::info('WalletMovements: Receptor verificado como directo válido', [
                'receiver_id' => $receiverId
            ]);

            // 3. VERIFICAR QUE LA WALLET DEL REMITENTE EXISTE
            $wallet = Wallet::where('user_id', $user->id)->first();

            if (!$wallet) {
                Log::error('WalletMovements: Wallet del remitente no encontrado', [
                    'sender_id' => $user->id
                ]);
                return response()->json(['error' => 'Wallet not found'], 404);
            }

            // 4. VALIDACIÓN CRÍTICA: VERIFICAR FONDOS SUFICIENTES EN BILLETERA
            $user_wallet_balance = app(CartController::class)->retrieveWalletBalanceUser($user->id);
            if ($user_wallet_balance < $amount) {
                Log::warning('WalletMovements: Fondos insuficientes', [
                    'sender_id' => $user->id,
                    'saldo_actual' => $user_wallet_balance,
                    'monto_requerido' => $amount,
                    'faltante' => $amount - $user_wallet_balance
                ]);

                return response()->json([
                    'error' => 'Fondos insuficientes en la billetera',
                    'saldo_actual' => '$' . number_format($user_wallet_balance, 2),
                    'monto_requerido' => '$' . number_format($amount, 2),
                    'faltante' => '$' . number_format($amount - $user_wallet_balance, 2)
                ], 400);
            }

            Log::info('WalletMovements: Fondos verificados correctamente', [
                'wallet_id' => $wallet->id,
                'sender_id' => $user->id,
                'balance_available' => $user_wallet_balance,
                'amount_to_transfer' => $amount
            ]);

            // 5. VERIFICAR QUE EL USUARIO RECEPTOR EXISTE
            $myDirect = User::where('id', $receiverId)->first();

            if (!$myDirect) {
                Log::error('WalletMovements: Usuario receptor no encontrado', [
                    'receiver_id' => $receiverId
                ]);
                return response()->json(['error' => 'Receiver not found'], 404);
            }

            // 6. VERIFICAR QUE EL RECEPTOR TIENE WALLET
            $receiverWallet = Wallet::where('user_id', $receiverId)->first();

            if (!$receiverWallet) {
                Log::error('WalletMovements: Wallet del receptor no encontrado', [
                    'receiver_id' => $receiverId
                ]);
                return response()->json(['error' => 'Receiver wallet not found'], 404);
            }

            Log::info('WalletMovements: Usuario receptor encontrado', [
                'receiver_id' => $myDirect->id,
                'receiver_username' => $myDirect->username
            ]);

            $last_batch = Option::lastBatch();
            $last_batch = (int) $last_batch->value;

            Log::info('WalletMovements: Batch obtenido', [
                'batch' => $last_batch
            ]);

            // 7. INICIAR TRANSACCIÓN ATÓMICA
            DB::beginTransaction();

            Log::info('WalletMovements: Transacción iniciada para transferencia');

            // 8. CREAR MOVIMIENTO DE DÉBITO PARA EL REMITENTE
            $debitMovement = new WalletMovements();
            $debitMovement->wallet_id = $wallet->id;
            $debitMovement->amount = -$amount; // Negativo para débito
            $debitMovement->type = 0; // Transfer out
            $debitMovement->batch = $last_batch;
            $debitMovement->id_receiver = $myDirect->id;
            $debitMovement->reason = 'Transfer of funds from ' . $user->username . ' to ' . $myDirect->username;

            if (!$debitMovement->save()) {
                throw new \Exception('Error al guardar movimiento de débito');
            }

            Log::info('WalletMovements: Movimiento de débito guardado exitosamente', [
                'movement_id' => $debitMovement->id,
                'wallet_id' => $wallet->id,
                'amount' => -$amount,
                'sender' => $user->username,
                'receiver' => $myDirect->username,
                'batch' => $last_batch
            ]);

            // 9. CREAR MOVIMIENTO DE CRÉDITO PARA EL RECEPTOR
            $creditMovement = new WalletMovements();
            $creditMovement->wallet_id = $receiverWallet->id;
            $creditMovement->amount = $amount; // Positivo para crédito
            $creditMovement->type = 1; // Transfer in
            $creditMovement->batch = $last_batch;
            $creditMovement->id_receiver = $user->id; // El que envía
            $creditMovement->reason = 'Transfer of funds from ' . $user->username . ' to ' . $myDirect->username;

            if (!$creditMovement->save()) {
                throw new \Exception('Error al guardar movimiento de crédito');
            }

            Log::info('WalletMovements: Movimiento de crédito guardado exitosamente', [
                'movement_id' => $creditMovement->id,
                'wallet_id' => $receiverWallet->id,
                'amount' => $amount,
                'sender' => $user->username,
                'receiver' => $myDirect->username,
                'batch' => $last_batch
            ]);

            // 10. CONFIRMAR TRANSACCIÓN
            DB::commit();

            Log::info('WalletMovements: Transacción de transferencia completada exitosamente', [
                'debit_movement_id' => $debitMovement->id,
                'credit_movement_id' => $creditMovement->id,
                'amount_transferred' => $amount,
                'sender' => $user->username,
                'receiver' => $myDirect->username
            ]);

            // 11. OBTENER NUEVO BALANCE PARA LA RESPUESTA
            $new_balance = app(CartController::class)->retrieveWalletBalanceUser($user->id);

            return response()->json([
                'data' => [
                    'status' => 'ok',
                    'amount_transferred' => '$' . number_format($amount, 2),
                    'new_balance' => '$' . number_format($new_balance, 2),
                    'receiver' => $myDirect->username,
                    'batch' => $last_batch
                ],
                'message' => 'Transferencia realizada exitosamente',
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('WalletMovements: Error en transferencia de fondos', [
                'sender_id' => $user->id,
                'receiver_id' => $receiverId,
                'amount' => $amount,
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Error interno del servidor durante la transferencia'
            ], 500);
        }
    }

    public function requestFounds(Request $request)
    {
        $amount = $request->amount;
        $account_type = $request->account_type;
        $account_number = $request->account_number;
        $user = auth()->user();

        Log::info('WalletMovements: Iniciando solicitud de fondos', [
            'user_id' => $user->id,
            'username' => $user->username,
            'amount' => $amount,
            'account_type' => $account_type,
            'account_number' => $account_number
        ]);

        try {
            $admin = User::where('username', "admin")->first();
            
            if (!$admin) {
                Log::error('WalletMovements: Usuario admin no encontrado');
                return response()->json(['error' => 'Admin user not found'], 500);
            }

            Log::info('WalletMovements: Usuario admin encontrado', [
                'admin_id' => $admin->id
            ]);

            $myWallet = Wallet::where('user_id', $user->id)->first();
            
            if (!$myWallet) {
                Log::error('WalletMovements: Wallet del usuario no encontrado', [
                    'user_id' => $user->id
                ]);
                return response()->json(['error' => 'Wallet not found'], 404);
            }

            Log::info('WalletMovements: Wallet del usuario encontrado', [
                'wallet_id' => $myWallet->id,
                'user_id' => $user->id
            ]);

            $last_batch = Option::lastBatch();
            $last_batch = (int) $last_batch->value;

            Log::info('WalletMovements: Batch obtenido para solicitud', [
                'batch' => $last_batch
            ]);

            DB::beginTransaction();

            Log::info('WalletMovements: Transacción iniciada para solicitud de fondos');

            $movement = new WalletMovements();
            $movement->wallet_id = $myWallet->id;
            $movement->amount = $amount;
            $movement->type = 0;
            $movement->batch = $last_batch;
            $movement->status = 0;
            $movement->reason = 'Solicitud de fondos';
            $movement->account_type = $account_type;
            $movement->account_number = $account_number;

            if ($movement->save()) {
                Log::info('WalletMovements: Movimiento de solicitud guardado exitosamente', [
                    'movement_id' => $movement->id,
                    'wallet_id' => $myWallet->id,
                    'amount' => $amount,
                    'user_id' => $user->id,
                    'account_type' => $account_type,
                    'batch' => $last_batch
                ]);
                $response['status'] = 'ok';
            } else {
                Log::error('WalletMovements: Error al guardar movimiento de solicitud', [
                    'wallet_id' => $myWallet->id,
                    'amount' => $amount,
                    'user_id' => $user->id
                ]);
                $response['status'] = 'error';
            }

            $notification = new Notifications();
            $notification->id_generator = $user->id;
            $notification->id_receiver = $admin->id;
            $notification->title = "Solicitud de Fondos";
            $notification->body = $user->name . " solicita el retiro de $ " . $amount;
            $notification->type = 1;

            if ($notification->save()) {
                Log::info('WalletMovements: Notificación de solicitud guardada exitosamente', [
                    'notification_id' => $notification->id,
                    'generator_id' => $user->id,
                    'receiver_id' => $admin->id,
                    'amount' => $amount
                ]);
                $response['status'] = 'ok';
            } else {
                Log::error('WalletMovements: Error al guardar notificación de solicitud', [
                    'generator_id' => $user->id,
                    'receiver_id' => $admin->id,
                    'amount' => $amount
                ]);
                $response['status'] = 'error';
            }

            DB::commit();

            Log::info('WalletMovements: Transacción de solicitud de fondos completada exitosamente', [
                'movement_id' => $movement->id ?? null,
                'notification_id' => $notification->id ?? null,
                'status' => $response['status']
            ]);

            return response()->json([
                'data' => $response,
                'message' => 'Operación exitosa',
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('WalletMovements: Error en solicitud de fondos', [
                'user_id' => $user->id,
                'amount' => $amount,
                'account_type' => $account_type,
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString()
            ]);
            throw $th;
        }
    }

    public function requestFoundsList()
    {
        Log::info('WalletMovements: Iniciando obtención de lista de solicitudes', [
            'authenticated_user' => auth()->id()
        ]);

        try {
            $requests = WalletMovements::with(['wallet' => function($query){
                $query->with(['user']);
            }])->where('bonus_type_id', null)
                ->where('status', 0)->get();

            Log::info('WalletMovements: Lista de solicitudes obtenida exitosamente', [
                'requests_count' => $requests->count(),
                'authenticated_user' => auth()->id()
            ]);

            return $requests;

        } catch (\Exception $e) {
            Log::error('WalletMovements: Error al obtener lista de solicitudes', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'authenticated_user' => auth()->id()
            ]);
            
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    public function rejectRequest(Request $request)
    {
        $requestId = $request->id;

        Log::info('WalletMovements: Iniciando rechazo de solicitud', [
            'request_id' => $requestId,
            'authenticated_user' => auth()->id()
        ]);

        try {
            $wallet_movement = WalletMovements::findOrFail($requestId);

            Log::info('WalletMovements: Movimiento encontrado para rechazo', [
                'movement_id' => $wallet_movement->id,
                'wallet_id' => $wallet_movement->wallet_id,
                'amount' => $wallet_movement->amount,
                'current_status' => $wallet_movement->status
            ]);

            $wallet_movement->status = 2;
            $wallet_movement->update();

            Log::info('WalletMovements: Solicitud rechazada exitosamente', [
                'movement_id' => $wallet_movement->id,
                'new_status' => 2,
                'authenticated_user' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Solicitud rechazada exitosamente'
            ], 200);

        } catch (\Exception $e) {
            Log::error('WalletMovements: Error al rechazar solicitud', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'authenticated_user' => auth()->id()
            ]);
            
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    public function approveRequest(Request $request)
    {
        $requestId = $request->id;

        Log::info('WalletMovements: Iniciando aprobación de solicitud', [
            'request_id' => $requestId,
            'authenticated_user' => auth()->id(),
            'has_support_image' => $request->hasFile('support_image'),
            'message' => $request->message
        ]);

        try {
            $wallet_movement = WalletMovements::findOrFail($requestId);

            Log::info('WalletMovements: Movimiento encontrado para aprobación', [
                'movement_id' => $wallet_movement->id,
                'wallet_id' => $wallet_movement->wallet_id,
                'amount' => $wallet_movement->amount,
                'current_status' => $wallet_movement->status,
                'current_support_image' => $wallet_movement->support_image
            ]);

            if ($request->hasFile('support_image')) {
                Log::info('WalletMovements: Procesando imagen de soporte');
                
                $image = $request->file('support_image');
                $formattedFilename = Helper::formatFilename($image->getClientOriginalName());
                $path = 'support_images/' . $formattedFilename;

                Log::info('WalletMovements: Detalles de la imagen', [
                    'original_filename' => $image->getClientOriginalName(),
                    'formatted_filename' => $formattedFilename,
                    'path' => $path,
                    'file_size' => $image->getSize()
                ]);

                if ($wallet_movement->support_image) {
                    $existingPath = str_replace(env('APP_URL') . '/storage/', '', $wallet_movement->support_image);
                    Storage::disk('s3')->delete($existingPath);
                    
                    Log::info('WalletMovements: Imagen anterior eliminada', [
                        'deleted_path' => $existingPath
                    ]);
                }

                $options = [
                    'visibility' => 'public',
                    'ContentDisposition' => 'attachment; filename="' . $formattedFilename . '"',
                ];

                Storage::disk('s3')->put($path, file_get_contents($image), $options);
                $wallet_movement->support_image = Storage::disk('s3')->url($path);

                Log::info('WalletMovements: Nueva imagen guardada exitosamente', [
                    'new_image_url' => $wallet_movement->support_image,
                    'path' => $path
                ]);
            }

            $wallet_movement->message = $request->message;
            $wallet_movement->status = 1;
            $wallet_movement->update();

            Log::info('WalletMovements: Solicitud aprobada exitosamente', [
                'movement_id' => $wallet_movement->id,
                'new_status' => 1,
                'message' => $request->message,
                'support_image_updated' => $request->hasFile('support_image'),
                'authenticated_user' => auth()->id()
            ]);

            return response()->json([
                'message' => 'Solicitud aprobada exitosamente'
            ], 200);

        } catch (\Exception $e) {
            Log::error('WalletMovements: Error al aprobar solicitud', [
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'authenticated_user' => auth()->id()
            ]);
            
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }
}