<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Point;
use App\Models\Course;
use App\Models\Option;
use App\Models\Wallet;
use App\Helpers\Helper;
use App\Models\Country;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Classified;
use App\Models\AccountType;
use App\Models\SponsorLink;
use App\Models\Certificates;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use App\Models\Notifications;
use App\Models\PaymentMethod;
use App\Mail\RegisterMailable;
use App\Models\UserDailyQuizz;
use App\Traits\ResponseFormat;
use App\Models\WalletMovements;
use App\Models\AccountTypeDetail;
use App\Models\UnverifiedPayment;
use App\Models\UserConfiguration;
use App\Http\Requests\UserRequest;
use App\Models\UserClassroomPoint;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Mail\EmailRecoveryMailable;
use Illuminate\Support\Facades\Log;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Models\AccountTypePointsMoney;
use Illuminate\Support\Facades\Storage;
use App\Models\AccountTypeDetailHistory;
use App\Http\Controllers\ShareLinkController;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Services\PHPMailerService;

class UserController extends Controller
{
    use ResponseFormat;

    public function __construct()
    {
        $this->middleware('auth:api')->only(['show']);
    }

    public function viewProfileSetting()
    {
        return view('content.setting.profile');
    }

    // Vista para registrar nuevo usuario
    public function RegisterSponsor(Request $request)
    {
        try {
            //Log::info("Parámetros recibidos:", [
            //    'id' => $request->id,
            //    'code' => $request->code,
            //    'hash' => $request->route()->parameter('hash') ?? 'no hash'
            //]);

            // Buscar usuario por ID (el primer parámetro de la URL)
            $user = User::find($request->id);
            
            if (!$user) {
                Log::error("Usuario no encontrado con ID: " . $request->id);
                return redirect()->back()->with('error', 'Enlace inválido - Usuario no encontrado');
            }

           //Log::info("Usuario encontrado:", [
           //    'user_id' => $user->id,
           //    'user_name' => $user->name ?? 'N/A'
           //]);

            // Buscar enlace activo que contenga el timestamp en la URL
            $userLink = SponsorLink::where('user_id', $user->id)
                ->where('url', 'like', '%/' . $request->id . '/' . $request->code . '%')
                ->where('fecha_fin', '>', Carbon::now())
                ->where('estado', true)
                ->first();

            if (!$userLink) {
                Log::warning("No se encontró enlace activo", [
                    'user_id' => $user->id,
                    'timestamp_buscado' => $request->code
                ]);
                
                // Debug adicional: mostrar todos los enlaces de este usuario
                $allUserLinks = SponsorLink::where('user_id', $user->id)->get();
                //Log::info("Enlaces existentes para usuario:", [
                //    'user_id' => $user->id,
                //    'enlaces' => $allUserLinks->toArray()
                //]);
                
                return redirect()->back()->with('error', 'Enlace expirado o inválido');
            }

            // Verificar que el enlace no esté expirado
            if (Carbon::now()->gt($userLink->fecha_fin)) {
                //Log::warning("Enlace expirado", [
                //    'link_id' => $userLink->id,
                //    'fecha_fin' => $userLink->fecha_fin,
                //    'now' => Carbon::now()
                //]);
                return redirect()->back()->with('error', 'Este enlace ha expirado');
            }

            //Log::info("Enlace válido encontrado:", [
            //    'link_id' => $userLink->id,
            //    'url' => $userLink->url
            //]);

            $purchase_number = Helper::generatePurchaseCode();

            // Obtener datos necesarios para la vista
            $document_type = DocumentType::select('id', 'document')->get();
            $account_type = AccountType::select('id', 'account', 'price', 'iva')->where('status', '1')->get();
            $country = Country::select('id', 'name')->get();
            $payment_methods = PaymentMethod::select('id', 'name')
                ->where('status', 1)
                ->whereIn('name', ['Binance', 'Tarjeta crédito / débito'])
                ->get();
            $user_type = Role::where('name', '!=', 'Admin')->get();

            $payment = Payment::all()->count() + 2;

            // Calcular totales
            for ($i = 0; $i < sizeof($account_type); $i++) {
                $account_type[$i]["total"] = $account_type[$i]->price + ($account_type[$i]->price * ($account_type[$i]->iva / 100));
            }

            $key_openpay = env('OPENPAY_SK_ENCODED');
            $id_openpay = env('OPENPAY_ID');

            return view('content.user-membreship.register', [
                'purchase_number' => $purchase_number,
                'ip_address' => $request->ip(),
                'document_type' => $document_type,
                'account_type' => $account_type,
                'country' => $country,
                'id_referrer_sponsor' => $user->id,
                'sponsor_name' => $user->name,
                'payment_methods' => $payment_methods,
                'user_type' => $user_type,
                'key_openpay' => $key_openpay,
                'id_openpay' => $id_openpay
            ]);

        } catch (\Exception $e) {
            Log::error('Error en RegisterSponsor', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()->with('error', 'Ha ocurrido un error interno.');
        }
    }

    public function List()
    {
        return view('content.user-membreship.list');
    }

    public function GetList(Request $request): AnonymousResourceCollection
    {
        $list_user_membreship = User::query()
            ->with(['accountType', 'documentType', 'country', 'classifiedSponsor.user.accountType'])
            ->where('request', 2)
            ->join('classified', 'users.id', '=', 'classified.user_id')
            ->get();

        $list_user_membreship->each->append('qualified');

        return JsonResource::collection($list_user_membreship);
    }

    public function validateUser(UserRequest $request)
    {
        $body = $request->all();
        $password = $body['password'] ?? '';
        unset($body['password']);
        session(['body' => $body]);
        session(['user_password' => $password]);
        app(UserController::class)->Create($request->order_id);
        return json_encode($response['ruta'] = url());
    }

    public function recompraUpdate($order_id)
    {

        $user = auth()->user();
        if (!$user) {
            $user_id = UnverifiedPayment::where('openpay_order_id', $order_id)
                ->pluck('user_id');
            $user = User::findOrFail($user_id[0]);
        }

        // store payment
        $payment = new Payment();
        $payment->user_id = $user->id;
        $payment->id_user_sponsor = $user->id_referrer_sponsor;
        $product = Product::where('name', 'opc')->where('account_type_id', $user->id_account_type)->first() ?? Product::where('name', '=', 'opc')->get()->first();
        $singular_price = $product->price;
        $total_paid = UnverifiedPayment::where('openpay_order_id', $order_id)->value('product_price');
        
        // Si no viene de OpenPay (ej. Niubiz o PayPal), asumimos que pagó 1 cuota ($singular_price)
        $total_paid = $total_paid ?? $singular_price;
        
        $payment->amount = $total_paid;
        $payment->operation_number = $order_id;
        $payment->id_payment_method = 1;
        $payment->details = "Recompra de OPC";
        $payment->save();

        $userUpdate = User::where('id', $user->id)->get()->first();

        Log::info('recompraUpdate: Fechas antes del calculo', [
            'order_id' => $order_id,
            'user_id' => $user->id,
            'id_account_type' => $user->id_account_type,
            'old_expiration' => $userUpdate->expiration_date,
            'total_paid' => $total_paid,
            'singular_price' => $singular_price
        ]);

        if ($user->id_account_type == 5 || $user->id_account_type == 6) {
            $userPromotionDays = Carbon::now();
        } else {
            // SIEMPRE sumar días a la fecha de expiración existente (incluso si está vencida).
            // Ejemplo: si debe 3 meses (exp: Mar 18) y paga 1 cuota:
            //   Mar 18 + 30 = Abr 17 → aún debe 2 meses. CORRECTO.
            // Si usáramos Carbon::now(): Hoy + 30 = Jul 16 → deuda = 0. INCORRECTO.
            $userPromotionDays = Carbon::parse($userUpdate->expiration_date);
        }
        $multiplier = round($total_paid / $singular_price);
        $userPromotionDays->addMonths($multiplier);
        $userUpdate->expiration_date = $userPromotionDays;
        
        Log::info('recompraUpdate: Fechas despues del calculo', [
            'new_expiration' => $userUpdate->expiration_date,
            'multiplier' => $multiplier
        ]);

        $userUpdate->save();

        $id = $user->id;
        $fullName = $user->name;
        $membersip = $user->id_account_type;
        $action_user = Classified::where('user_id', $id)->first();

        $save_position_branch = $action_user->position;

        $aux = false;

        if ($membersip != 5 && $membersip != 6) {
            $ancestor_id = $action_user->user_above;
            $ancestor_data = $ancestor_id ? Classified::where('user_id', $ancestor_id)->first() : null;
            $aux = (!$ancestor_data || $ancestor_data->user_above == null) ? true : false;

            while ($aux == false) {
                $ancestor_data = Classified::where('user_id', $ancestor_id)->first();
                if (!$ancestor_data) { break; }
                $aux = $ancestor_data->user_above == null ? true : false;
                $ancestor_status = User::find($ancestor_id);
                if ($ancestor_status && $ancestor_status->active && $ancestor_status->membershipActive) {
                    if ($ancestor_status->qualified) {
                        Point::create([
                            'user_id' => $user->id,
                            'sponsor_id' => $ancestor_data->user_id,
                            'points' => $product->points,
                            'side' => $save_position_branch,
                            'reason' => "OPC points, " . $fullName
                        ]);
                    } elseif ($action_user->id_user_sponsor == $ancestor_data->user_id) {
                        Point::create([
                            'user_id' => $user->id,
                            'sponsor_id' => $ancestor_data->user_id,
                            'points' => $product->points,
                            'side' => $save_position_branch,
                            'reason' => "OPC points, " . $fullName
                        ]);
                    }
                }

                $save_position_branch = $ancestor_data->position;
                $ancestor_id = $ancestor_data->user_above;
            }
        }
    }

    public function membershipUpdate($order_id, $membership_id)
    {
        DB::beginTransaction();
    
        try {
            Log::info('===> [membershipUpdate] Iniciando proceso', [
                'order_id' => $order_id,
                'membership_id' => $membership_id,
                'auth_user' => auth()->user()?->id ?? null
            ]);
        
            $user = auth()->user();
            if (!$user) {
                $user_id = UnverifiedPayment::where('openpay_order_id', $order_id)
                    ->pluck('user_id');
                Log::info('[membershipUpdate] Usuario no autenticado, recuperando desde UnverifiedPayment', [
                    'order_id' => $order_id,
                    'user_id_pluck' => $user_id
                ]);
            
                $user = User::findOrFail($user_id[0]);
            }
        
            Log::info('[membershipUpdate] Usuario encontrado', [
                'user_id' => $user->id,
                'old_membership' => $user->id_account_type,
                'new_membership' => $membership_id
            ]);
        
            $account_type = AccountType::find($membership_id);
            $base_price = $account_type->price;
            $iva_amount = $base_price * ($account_type->iva / 100);
            $total_amount = $base_price + $iva_amount;
        
            Log::info('[membershipUpdate] Creando registro de pago', [
                'amount' => $total_amount,
                'user_id' => $user->id
            ]);
        
            $payment = new Payment();
            $payment->user_id = $user->id;
            $payment->id_user_sponsor = $user->id_referrer_sponsor;
            $payment->amount = $total_amount;
            $payment->operation_number = $order_id;
            $payment->id_payment_method = 1;
            $payment->details = json_encode([
                'base_price' => $base_price,
                'iva' => $account_type->iva,
                'iva_amount' => $iva_amount,
                'description' => "Recompra de membresía: " . $account_type->account
            ]);
            $payment->save();
        
            Log::info('[membershipUpdate] Pago guardado correctamente', [
                'payment_id' => $payment->id
            ]);
        
            // ===> Actualizar fechas de membresía
            if (in_array($user->id_account_type, [5, 6])) {
                $userRenewMembership = now();
            } else {
                try {
                    $userRenewMembership = Carbon::parse($user->expiration_membership_date);
                } catch (\Exception $ex) {
                    Log::warning('[membershipUpdate] Error parseando expiration_membership_date, usando fecha actual', [
                        'user_id' => $user->id,
                        'error' => $ex->getMessage()
                    ]);
                    $userRenewMembership = now();
                }
            }
            $userRenewMembership->addDays(365);
        
            $userUpdate = User::find($user->id);
            $userUpdate->id_account_type = $membership_id;
            $userUpdate->expiration_membership_date = $userRenewMembership;
        
            // ===> NUEVA LÓGICA DE expiration_date
            if ($user->expiration_date == '9999-12-31 23:59:59') {
                // Si la fecha es "infinita", usar fecha actual + 30 días
                $userRenewOPC = now()->addDays(30);
                Log::info('[membershipUpdate] Expiration_date era 9999-12-31, actualizando con +30 días', [
                    'user_id' => $user->id,
                    'old_expiration_date' => $user->expiration_date,
                    'new_expiration_date' => $userRenewOPC->toDateTimeString()
                ]);
            } else {
                try {
                    $userRenewOPC = Carbon::parse($user->expiration_date)->addDays(30);
                } catch (\Exception $ex) {
                    Log::warning('[membershipUpdate] Error al parsear expiration_date, usando fecha actual', [
                        'user_id' => $user->id,
                        'expiration_date' => $user->expiration_date,
                        'error' => $ex->getMessage()
                    ]);
                    $userRenewOPC = now()->addDays(30);
                }
            }
        
            $userUpdate->expiration_date = $userRenewOPC;
            $userUpdate->save();
        
            // ===> Calcular puntos
            $atm = AccountTypePointsMoney::where('account_type_id', $membership_id)->first();
            $action_user = Classified::where('user_id', $user->id)->first();
            $save_position_branch = $action_user->position;
            $aux = false;
        
            if (!in_array($membership_id, [5, 6])) {
                $ancestor_id = $action_user->user_above;
                $contador = 0;
            
                while ($ancestor_id && !$aux) {
                    $ancestor_data = Classified::where('user_id', $ancestor_id)->first();
                    if (!$ancestor_data) {
                        Log::warning('[membershipUpdate] Ancestor data no encontrado', [
                            'ancestor_id' => $ancestor_id
                        ]);
                        break;
                    }
                
                    $aux = $ancestor_data->user_above == null;
                    $ancestor_status = User::find($ancestor_id);
                
                    if ($ancestor_status && $ancestor_status->active && $ancestor_status->membershipActive) {
                        if ($ancestor_status->qualified || $action_user->id_user_sponsor == $ancestor_data->user_id) {
                            Point::create([
                                'user_id' => $user->id,
                                'sponsor_id' => $ancestor_data->user_id,
                                'points' => $atm->points,
                                'side' => $save_position_branch,
                                'reason' => "Membership buy, " . $user->name
                            ]);
                        }
                    }
                
                    $save_position_branch = $ancestor_data->position;
                    $ancestor_id = $ancestor_data->user_above;
                    $contador++;
                }
            
                Log::info('[membershipUpdate] Finalizado recorrido de ancestros', [
                    'user_id' => $user->id,
                    'ancestors_processed' => $contador
                ]);
            
                // Bono de efectivo rápido
                if ($user->id_referrer_sponsor != 1) {
                    $last_batch = (int) Option::lastBatch()->value;
                    $id_account_type_sponsor = User::where('id', $user->id_referrer_sponsor)
                        ->value('id_account_type');
                    $fast_cash_sponsor = AccountType::where('id', $id_account_type_sponsor)
                        ->value('fast_cash_bonus');
                    $walletParentDirect = Wallet::where('user_id', $user->id_referrer_sponsor)->first();
                
                    if ($walletParentDirect) {
                        $movement = new WalletMovements();
                        $movement->wallet_id = $walletParentDirect->id;
                        $movement->amount = $base_price * ($fast_cash_sponsor / 100);
                        $movement->type = 1;
                        $movement->batch = $last_batch;
                        $movement->bonus_type_id = 1;
                        $movement->reason = 'Bono de efectivo rápido de ' . $user->username;
                        $movement->save();
                    
                        Log::info('[membershipUpdate] Bono rápido registrado', [
                            'wallet_movement_id' => $movement->id,
                            'sponsor_id' => $user->id_referrer_sponsor
                        ]);
                    }
                }
            }
        
            // ===> Actualizar ACCOUNT_TYPE_DETAIL
            $this->updateUserMembershipExpirationDate($user->id, $account_type->id);
        
            // 🆕 ENVIAR COMPROBANTE DE PAGO DE MEMBRESÍA
            $this->sendMembershipReceipt(
                $user->id,
                $membership_id,
                $total_amount,
                $base_price,
                $iva_amount,
                $account_type->iva,
                'OpenPay',
                $order_id
            );
        
            Log::info('[membershipUpdate] Actualización de membresía completada exitosamente', [
                'user_id' => $user->id,
                'membership_id' => $membership_id,
                'new_expiration_date' => $userRenewOPC->toDateTimeString()
            ]);
        
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[membershipUpdate] Error: ' . $e->getMessage(), [
                'order_id' => $order_id,
                'membership_id' => $membership_id,
                'trace_line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            throw $e;
        }
    }

    public function membershipUpdateBasic()
    {

        $user = auth()->user();

        $userUpdate = User::where('id', $user->id)->get()->first();
        $userUpdate->id_account_type = 5;
        $userUpdate->save();

        //Actualiza el registro de la fecha de expiracion de su memebresia en la tabla ACCOUNT_TYPE_DETAIL
        $this->updateUserMembershipExpirationDate($user->id, 5);
    }

    public function getUserStatus($payment_method_id)
    {
        switch ($payment_method_id) {
            case 1: #openpay
                return 2;
            case 4: #paypal
                return 2; # ingreso automático al sistema
            case 3: # transferencia
                return 1; # requiere aprobación
            default:
                return 1; # requiere aprobación
        }
    }

    public function getMyCredits()
    {
        $user = auth()->user();
    
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Usuario no autenticado'
            ], 401);
        }
    
        return response()->json([
            'status' => 'ok',
            'credits' => $user->credits,
            'formatted' => $user->formatted_credits
        ], 200);
    }

    public  function getLastUserBeforeEmpty($startingUserId, $position = 'user_position_left')
    {
        $current = $startingUserId;
        $lastValid = null;

        while ($current) {
            $classified = Classified::where('user_id', $current)->first();
            if (!$classified)
                break;

            $lastValid = $classified->user_id;

            $next = null;
            if ($position === 'user_position_left') {
                $next = Classified::where('user_above', $current)->where('position', 0)->first();
            } else {
                $next = Classified::where('user_above', $current)->where('position', 1)->first();
            }

            if (!$next)
                break;
            $current = $next->user_id;
        }

        return $lastValid;
    }

    public function Create($order_id)
    {
        $request = session()->get('body');
        $payment_method_id = $request['payment_method_id'];
        $tbRequest = $this->getUserStatus($payment_method_id);
    
        DB::transaction(function () use ($request, $tbRequest, $order_id) {
        
            $photo = Option::where('description', 'default_avatar')->select('value')->first();
            $photo = 'images/' . $photo->value;
        
            $account_type = AccountType::where('id', $request["id_account_type"])->first();
        
            $user = new User();
            $user->username = $request["username"];
            $password = session('user_password', $request["password"] ?? '');
            $alreadyEncrypted = session('encrypted_pass', false);
            $user->password = $alreadyEncrypted ? $password : Hash::make($password);
            $user->name = $request["name"];
            $user->last_name = $request["last_name"];
            $user->phone = $request["phone"];
            $user->date_birth = $request["date_birth"];
            $user->email = $request["email"];
            $user->id_referrer_sponsor = $request["id_referrer_sponsor"];
            $user->id_country = $request["id_country"];
            $user->city = 'ciudad';
            $user->id_document_type = $request["id_document_type"];
            $user->id_account_type = $request["id_account_type"];
            $user->nro_document = $request["nro_document"];
            $user->biography = $request["biography"];
            $user->request = $tbRequest;
            if (array_key_exists('binary_position', $request)) {
                $user->position = (int) $request['binary_position'];
            }
            $user->photo = $photo;
            $user->expiration_date = strtotime('+30 days');
            $user->expiration_membership_date = strtotime('+365 days');
            $user->save();
        
            Log::info("[USER CREATE] Usuario creado", [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email
            ]);
        
            $this->deleteSharedLink($request["id_referrer_sponsor"]);
        
            $account_type = AccountType::find($request["id_account_type"]);
        
            $user->assignRole($request["user_type"]);
            $id_user = $user->id;
        
            /* Crear billetera*/
            Wallet::create([
                'user_id' => $id_user,
                'status' => 1
            ]);
            Log::info("[USER CREATE] Wallet creada para usuario", ['user_id' => $id_user]);
        
            //Se crea registro en la tabla user_daily_quizzs
            $user_daily_quizz = new UserDailyQuizz();
            $user_daily_quizz->id_user = $id_user;
            $user_daily_quizz->passed_quizz = 0;
            $user_daily_quizz->save();
            Log::info("[USER CREATE] Registro en user_daily_quizz creado", ['user_id' => $id_user]);
        
            // store payment
            $payment = new Payment();
            $payment->user_id = $id_user;
            $payment->id_user_sponsor = $request["id_referrer_sponsor"];
            $payment->amount = $account_type->price;
            $payment->operation_number = $order_id;
            $payment->id_payment_method = 1;
            $payment->details = "Compra de membresía: " . $account_type->account;
            $payment->save();
            Log::info("[USER CREATE] Pago registrado", [
                'user_id' => $id_user,
                'payment_id' => $payment->id
            ]);
        
            $user_referrer_position = User::select('username', 'position')
                ->where('id', $request["id_referrer_sponsor"])->first();
            $binary_position = array_key_exists('binary_position', $request)
                ? (int) $request['binary_position']
                : (int) $user_referrer_position->position;
        
            $this->saveUserMembershipExpirationDate($id_user, $account_type->id);
            Log::info("[USER CREATE] Fecha de expiración de membresía guardada", ['user_id' => $id_user]);
        
            //Se crea registro en la tabla user_classroom_points
            $user_classroom_point = new UserClassroomPoint();
            $user_classroom_point->id_user = $id_user;
            $user_classroom_point->total_points = 0;
            $user_classroom_point->save();
            Log::info("[USER CREATE] Registro en user_classroom_points creado", ['user_id' => $id_user]);
        
            //algoritmo para busqueda de espacio vacio
            $position = $binary_position == 0 ? 'user_position_left' : 'user_position_right';
            $user_above = $this->getLastUserBeforeEmpty($request["id_referrer_sponsor"], $position);
        
            $fieldsClassifieds = [
                'user_id' => $id_user,
                'id_user_sponsor' => $request["id_referrer_sponsor"],
                'binary_sponsor' => $user_referrer_position->username,
                'position' => $binary_position,
                'classification' => 16,
                'status' => '0',
                'authorized' => '0',
                'user_above' => $user_above,
            ];
        
            $classified = Classified::create($fieldsClassifieds);
            Log::info("[USER CREATE] Registro en Classified creado", ['user_id' => $id_user, 'classified_id' => $classified->id]);
        
            $notification = new Notifications();
            $notification->id_generator = $id_user;
            $notification->id_receiver = $request["id_referrer_sponsor"];
            $notification->title = "Registro de Nuevo Afiliado";
            $notification->body = $user->name . ' ' . $user->last_name . ' se acaba de registrar con tu enlace';
            $notification->type = 1;
            $notification->save();
            Log::info("[USER CREATE] Notificación creada", ['user_id' => $id_user, 'notification_id' => $notification->id]);
        
            $status = 2;
            $id = $id_user;
            app(UserRequestController::class)->updateRequest($status, $id);
            Log::info("[USER CREATE] Solicitud de usuario actualizada", ['user_id' => $id_user]);
        
            if ($user->id_account_type != '9') {
                try {
                    $this->sendMailRegisteredUser($user->email, $user->username, $password);
                    Log::info("[USER CREATE] Correo enviado", ['user_id' => $id_user]);
                } catch (\Exception $e) {
                    Log::error("[USER CREATE] Error enviando correo", [
                        'user_id' => $id_user,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }, 9);
    
        DB::commit();
        session()->forget('body');
        session()->forget('user_password');
        session()->forget('encrypted_pass');
    }

    public function CreateFree(Request $request)
    {

        $tbRequest = 2;

        Log::info("Inicio CreateFree", [
            'id_account_type' => $request["id_account_type"],
            'tbRequest' => $tbRequest
        ]);

        DB::transaction(function () use ($request, $tbRequest) {

            $photo = Option::where('description', 'default_avatar')->select('value')->get()->first();
            $photo = 'images/' . $photo->value;

            // Creación del usuario
            $user = new User();
            $user->username = $request["username"];
            $user->password = Hash::make($request["password"]);
            $user->name = $request["name"];
            $user->last_name = $request["last_name"];
            $user->phone = $request["phone"];
            $user->date_birth = $request["date_birth"];
            $user->email = $request["email"];
            $user->id_referrer_sponsor = $request["id_referrer_sponsor"];
            $user->id_country = $request["id_country"];
            $user->city = 'ciudad';
            $user->id_document_type = $request["id_document_type"];
            $user->id_account_type = $request["id_account_type"];
            $user->nro_document = $request["nro_document"];
            $user->biography = $request["biography"];
            $user->request = $tbRequest; // Siempre será 2 para aprobación inmediata
            $user->expiration_date = strtotime('+10 years');
            $user->expiration_membership_date = strtotime('+365 days');
            Log::info("[USER CREATE] Buscando avatar por defecto...");
            $optionAvatar = Option::where('description', 'default_avatar')->select('value')->first();
            Log::info("[USER CREATE] Resultado query Option", ['option_avatar' => $optionAvatar]);

            if ($optionAvatar) {
                $photo = 'images/' . $optionAvatar->value;
                Log::info("[USER CREATE] Avatar asignado", ['photo' => $photo]);
            } else {
                Log::warning("[USER CREATE] No se encontró avatar por defecto en options");
                $photo = null; // O asigna un valor hardcodeado
            }

            $user->photo = $photo;
            Log::info("Antes de guardar usuario", [
                'id_account_type' => $user->id_account_type,
                'request' => $user->request
            ]);

            $user->save();

            // Eliminamos el enlace compartido del patrocinador
            $this->deleteSharedLink($request["id_referrer_sponsor"]);

            // Asignamos el tipo de cuenta y rol
            $account_type = AccountType::find($request["id_account_type"]);
            $user->assignRole($request["user_type"]);
            $id_user = $user->id;

            // Creación de la billetera
            Wallet::create([
                'user_id' => $id_user,
                'status' => 1
            ]);

            // Creación del registro de quizz diario
            $user_daily_quizz = new UserDailyQuizz();
            $user_daily_quizz->id_user = $id_user;
            $user_daily_quizz->passed_quizz = 0;
            $user_daily_quizz->save();

            // Obtenemos la posición del patrocinador
            $user_referrer_position = User::select('username', 'position')
                ->where('id', $request["id_referrer_sponsor"])->first();

            // Creamos el registro de expiración de membresía
            $this->saveUserMembershipExpirationDate($id_user, $account_type->id);

            // Creación de puntos de clase
            $user_classroom_point = new UserClassroomPoint();
            $user_classroom_point->id_user = $id_user;
            $user_classroom_point->total_points = 0;
            $user_classroom_point->save();

            // Búsqueda de posición vacía
            $position = $user_referrer_position->position == 0 ? 'user_position_left' : 'user_position_right';
            $user_above = $this->getLastUserBeforeEmpty($request["id_referrer_sponsor"], $position);


            $fieldsClassifieds = [
                'user_id' => $id_user,
                'id_user_sponsor' => $request["id_referrer_sponsor"],
                'binary_sponsor' => $user_referrer_position->username,
                'position' => $user_referrer_position->position,
                'classification' => 16,
                'status' => '0',
                'authorized' => '0',
                'user_above' => $user_above,
            ];

            $classified = Classified::create($fieldsClassifieds);

            // Creación de la notificación
            $notification = new Notifications();
            $notification->id_generator = $id_user;
            $notification->id_receiver = $request["id_referrer_sponsor"];
            $notification->title = "Registro de Nuevo Afiliado";
            $notification->body = $user->name . ' ' . $user->last_name . ' se acaba de registrar con tu enlace';
            $notification->type = 1;
            $notification->save();


            try {
                $this->sendMailRegisteredUser($user->email, $user->username, $request["password"]);
            } catch (\Exception $e) {
                Log::error("Error enviando correo:", [
                    'error' => $e->getMessage(),
                    'user_id' => $id_user,
                    'email' => $user->email
                ]);
            }

            Log::info("Usuario creado exitosamente", [
                'id_user' => $id_user,
                'id_account_type' => $user->id_account_type,
                'request' => $user->request
            ]);
        }, 5);

        DB::commit();

        $message = "El usuario se registró correctamente";

        // --- INICIO CÓDIGO DE DEPURACIÓN ---
        
        // 1. Capturamos la variable de entorno sola
        $envUrl = env('APP_URL');
        
        // 2. Construimos la URL como lo tienes actualmente
        $redirectUrl = $envUrl . 'login'; 

        // 3. Escribimos en el Log (storage/logs/laravel.log)
        Log::info("--- DEBUG REDIRECCION ---", [
            'env_APP_URL_raw' => $envUrl,       // Veremos qué trae exactamente el .env
            'url_concatenada' => $redirectUrl,  // Veremos la URL final rota
            'url_helper_laravel' => url('/login') // Veremos cómo lo generaría Laravel correctamente
        ]);
        
        // --- FIN CÓDIGO DE DEPURACIÓN ---

        // Retorna JSON con mensaje y la URL calculada
        return response()->json([
            'success' => true,
            'message' => $message,
            'redirect_url' => $redirectUrl 
        ]);
    }
    public function CreateFree1(Request $request)
    {
        $tbRequest = $request["id_account_type"] == 5 ? 2 : 1;

        DB::transaction(function () use ($request, $tbRequest) {

            $photo = Option::where('description', 'default_avatar')->select('value')->get()->first();
            $photo = 'images/' . $photo->value;

            $user = new User();
            $user->username = $request["username"];
            $user->password = Hash::make($request["password"]);
            $user->name = $request["name"];
            $user->last_name = $request["last_name"];
            $user->phone = $request["phone"];
            $user->date_birth = $request["date_birth"];
            $user->email = $request["email"];
            $user->id_referrer_sponsor = $request["id_referrer_sponsor"];
            $user->id_country = $request["id_country"];
            $user->city = 'ciudad';
            $user->id_document_type = $request["id_document_type"];
            $user->id_account_type = $request["id_account_type"];
            $user->nro_document = $request["nro_document"];
            $user->biography = $request["biography"];
            $user->request = $tbRequest;
            $user->expiration_date =  strtotime('+10 years');
            $user->expiration_membership_date = strtotime('+365 days');
            $user->photo = $photo;
            $user->save();

            $this->deleteSharedLink($request["id_referrer_sponsor"]);

            $account_type = AccountType::find($request["id_account_type"]);

            $user->assignRole($request["user_type"]);
            $id_user = $user->id; // Get ID of user

            /* Crear billetera*/
            Wallet::create([
                'user_id' => $id_user,
                'status' => 1
            ]);

            //Se crea registro en la tabla user_daily_quizzs
            $user_daily_quizz = new UserDailyQuizz();
            $user_daily_quizz->id_user = $id_user;
            $user_daily_quizz->passed_quizz = 0;
            $user_daily_quizz->save();

            $user_referrer_position = User::select('username', 'position')
                ->where('id', $request["id_referrer_sponsor"])->first();

            //Crea un registro de la fecha de expiracion de su memebresia en la tabla ACCOUNT_TYPE_DETAIL
            $this->saveUserMembershipExpirationDate($id_user, $account_type->id);

            //Se crea registro en la tabla user_classroom_points
            $user_classroom_point = new UserClassroomPoint();
            $user_classroom_point->id_user = $id_user;
            $user_classroom_point->total_points = 0;
            $user_classroom_point->save();

            //algoritmo para busqueda de espacio vacio
            $position = $user_referrer_position->position == 0 ? 'user_position_left' : 'user_position_right';
            $user_above = $this->search_empty_position($request, $user_referrer_position, $user, $position);

            $fieldsClassifieds  = [
                'user_id' => $id_user,
                'id_user_sponsor' => $request["id_referrer_sponsor"],
                'binary_sponsor' => $user_referrer_position->username,
                'position' => $user_referrer_position->position,
                'classification' => 16,
                'status' => '0',
                'authorized' => '0',
                'user_above' => $user_above,
            ];

            $classified = Classified::create($fieldsClassifieds);

            $notification = new Notifications();
            $notification->id_generator = $id_user;
            $notification->id_receiver = $request["id_referrer_sponsor"];
            $notification->title = "Registro de Nuevo Afiliado";
            $notification->body = $user->name . ' ' . $user->last_name . ' se acaba de registrar con tu enlace';
            $notification->type = 1;
            $notification->save();

            // Si el usuario tiene una membresia diferente a la basica no le enviara correo de solicitud pendiente
            if ($user->id_account_type != '5') {
                try {
                    $this->sendMailRegisteredUser($user->email, $user->username, $request["password"]);
                } catch (\Exception $e) {
                    return $e;
                }
            }
        }, 5);

        DB::commit();

        $message = "El usuario se registró correctamente";
        return json_encode($message);
    }

    public function deleteSharedLink($id)
    {

        $shareLink = SponsorLink::where('user_id', $id)->first();

        if ($shareLink) {

            app(ShareLinkController::class)->Delete($shareLink->id);
        }
    }

    public function createUnverifiedUser(UserRequest $request)
    {
        $tbRequest = $request["id_account_type"] == 5 ? 2 : 1;

        // Log de datos iniciales del request
        Log::info("📥 Datos recibidos para crear usuario:", [
            'username' => $request["username"],
            'email' => $request["email"],
            'id_referrer_sponsor' => $request["id_referrer_sponsor"],
            'id_account_type' => $request["id_account_type"],
            'payment_method_id' => $request["payment_method_id"],
            'operation_number' => $request["operation_number"]
        ]);

        try {
            DB::beginTransaction();

            $photo = Option::where('description', 'default_avatar')
                ->select('value')
                ->first();
            $photo = 'images/' . $photo->value;

            Log::info("📸 Avatar asignado: " . $photo);

            $user = new User();
            $user->username = $request["username"];
            $user->password = Hash::make($request["password"]);
            $user->name = $request["name"];
            $user->last_name = $request["last_name"];
            $user->phone = $request["phone"];
            $user->date_birth = $request["date_birth"];
            $user->email = $request["email"];
            $user->id_referrer_sponsor = $request["id_referrer_sponsor"];
            $user->id_country = $request["id_country"];
            $user->city = 'ciudad';
            $user->id_document_type = $request["id_document_type"];
            $user->id_account_type = $request["id_account_type"];
            $user->nro_document = $request["nro_document"];
            $user->biography = $request["biography"];
            $user->request = $tbRequest;
            $user->expiration_date = strtotime('+30 days');
            $user->expiration_membership_date = strtotime('+365 days');
            $user->photo = $photo;

            // Log antes de calcular la posición
            Log::info("🔍 Buscando referidor...", [
                'ref_id' => $request["id_referrer_sponsor"]
            ]);

            $referrer = User::find($request["id_referrer_sponsor"]);
            $user->position = $referrer && $referrer->position == 0 ? 1 : 0;

            Log::info("📌 Posición asignada al usuario: " . $user->position);

            $user->save();

            Log::info("✅ Usuario creado con ID: " . $user->id);

            $this->deleteSharedLink($request["id_referrer_sponsor"]);

            $account_type = AccountType::find($request["id_account_type"]);

            Log::info("📦 Tipo de cuenta asignado:", [
                'account_type' => $account_type->account,
                'price' => $account_type->price
            ]);

            $user->assignRole($request["user_type"]);

            if ($request->hasFile('receipt') && $request->payment_method_id == 3) {
                $file = $request->file('receipt');
                $filename = time() . '_' . $file->getClientOriginalName();
                $receipt_path = $file->storeAs('receipts', $filename, 'public');

                Log::info("🧾 Recibo cargado:", [
                    'filename' => $filename,
                    'path' => $receipt_path
                ]);
            }

            $payment = new Payment();
            $payment->user_id = $user->id;
            $payment->id_user_sponsor = $request["id_referrer_sponsor"];
            $payment->amount = $account_type->price;
            $payment->operation_number = $request["operation_number"];
            $payment->id_payment_method = $request["payment_method_id"];
            $payment->details = "Compra de membresía: " . $account_type->account;

            if (isset($receipt_path)) {
                $payment->receipt_image = $receipt_path;
            }

            $payment->save();

            Log::info("💰 Pago registrado:", [
                'payment_id' => $payment->id,
                'amount' => $payment->amount
            ]);

            if ($user->id_account_type != '5') {
                $this->sendMailRegisteredUser($user->email, $user->username, $request["password"]);
                Log::info("📧 Correo de registro enviado a: " . $user->email);
            }

            DB::commit();

            return json_encode("El usuario se registró correctamente, espere la confirmación del pago");

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error("❌ Error al registrar el usuario:", [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile()
            ]);
            return json_encode("Error al registrar el usuario: " . $th->getMessage());
        }
    }

    public function search_empty_position($request, $user_referrer_position, $user, $position)
    {
        $user_above = null;
        $referrer_sponsor_data = Classified::where('user_id', $request["id_referrer_sponsor"])
            ->first();

        if ($referrer_sponsor_data[$position] == null) {
            $referrer_sponsor_data[$position] = $user->id;
            $referrer_sponsor_data->update();
            $user_above = $request['id_referrer_sponsor'];
        } else {
            $user_above = $request['id_referrer_sponsor'];
        }
        return $user_above;
    }

    public function sendMailRegisteredUser($email, $user, $password)
    {
        $data = (object) array('username' => $user, 'password' => $password);
        $content = new RegisterMailable($data);

        Mail::to($email)
            ->cc($email)
            ->bcc($email)
            ->send($content);
    }

    public function getDataUser($user)
    {
        $data = User::where('username', $user)->with('accountType')->first();
        return response()->json($data, 200);
    }

    public function getDataUserId($id)
    {
        $data = User::where('id', $id)->with('accountType')->first();
        return response()->json($data, 200);
    }

    public function getPublicUserData($user)
    {
        $data = User::select('users.id', 'email', 'username', 'name', 'last_name', 'phone', 'photo', 'biography', 'id_account_type', 'sponsor_link.url')->join('sponsor_link', 'users.id', '=', 'sponsor_link.user_id')->where('username', $user)->first();
        return response()->json($data, 200);
    }

    public function getPublicCourse($id)
    {
        $data = Course::select('title', 'price', 'url_portada')->where('user_id', $id)->get();
        return response()->json($data, 200);
    }

    public function getDataCurrentUser()
    {
        $id = auth()->user()->id;
        $data = User::where('users.id', $id)
            ->join('country', 'users.id_country', '=', 'country.id')
            ->select('users.*', 'country.name AS countryName')
            ->get()
            ->first();
        return response()->json($data, 200);
    }

    public function changePositionCurrentUser(Request $request)
    {
        $user = User::find(auth()->user()->id);
        $user->position = $request->position;
        $user->update();
        return response()->json($user, 200);
    }

    public function credentials($purchase_operation_number, $purchase_amount = 0)
    {
        $acquirer_id = env('ACQUIRER_ID');
        $id_commerce = env('ID_COMMERCE');
        $purchase_password = env('PURCHASE_PASSWORD');
        $purchase_currency_code = env('PURCHASE_CURRENCY_CODE');
        $purchase_verification = openssl_digest($acquirer_id . $id_commerce . $purchase_operation_number . $purchase_amount . $purchase_currency_code . $purchase_password, 'sha512');
        return $purchase_verification;
    }

    public function update(Request $request)
    {
        $table = User::findOrFail($request->id);
        $table->name = $request->name;
        $table->nro_document = $request->nro_document;
        $table->id_document_type = $request->id_document_type;
        $table->phone = $request->phone;
        $table->email = $request->email;
        $table->update();

        return $this->responseOk('Usuario Actualizado');
    }

    public function updatePassword(Request $request)
    {
        $user = User::findOrFail(auth()->user()->id);
        $user->password = bcrypt($request->password);
        $user->update();

        return $this->responseOk('Contraseña Actualizada');
    }

    //Metodo: verifyDuplicate
    //METODO PARA COMPROBAR SI ALGUN CAMPO ESTA DUPLICADO
    //$field  es el nombre del campo en la base de datos  (ejem: email)
    //$value es el valor que se comprueba si esta duplicado (ejem: user2@gmail.com)
    //si el valor esta duplicado esta funcion retornara 1, caso contrario retorna 0

    public function verifyDuplicate(Request $request)
    {
        $field = $request->field;
        $value = $request->value;
        $bool = User::where($field, $value)->exists();
        if ($bool == 1) {
            return 1;
        }
        return 0;
    }

    public function show(Request $request)
    {
        // Solo permitir que el usuario vea sus propios datos
        $user = User::findOrFail(auth()->user()->id);

        // Filtrar campos sensibles que no deberían exponerse
        return $user->makeHidden([
            'password', 
            'remember_token', 
            'email_verified_at',
            'created_at',
            'updated_at',
            'nro_document' // Documento de identidad también es sensible
        ]);
    }

    public function myInfo()
    {
        $user = User::findOrFail(auth()->user()->id);
        return $user;
    }

    public function myPoints(Request $request)
    {
        $myPoints = UserClassroomPoint::where('user_classroom_points.id_user', $request->id)->select('user_classroom_points.total_points as total')->get()->first();
        return $myPoints;
    }

    public function uploadPhoto(Request $request)
    {
        $photo = $request->hasFile('user-photo') ? 1 : 0;
        $user = User::find(auth()->user()->id);
        if ($photo) {

            $path_photo = 'user_photos/' . $user->id;
            $ext_photo = $request->file('user-photo')->getClientOriginalExtension();
            $name_photo = 'profile_picture.' . $ext_photo;
            if (Storage::disk('s3')->exists($user->photo)) { //elimina la foto anterior
                Storage::disk('s3')->delete($user->photo);
            }
            // registra la foto con el id del usuario y la extension de la imagen.  ejemplo:   1.png
            $request->file('user-photo')->storeAs($path_photo, $name_photo, 's3');
            $user->photo = $path_photo . '/' . $name_photo;
            if ($user->save()) {
                return $this->responseOk('', "ok");
            } else {
                return $this->responseOk('', "ocurrio un error");
            }
        } else {
            return $this->responseOk('', "Ingrese una photo");
        }
    }

    public function storage_file($user)
    {
        $storage_domain = config('global_variables.storage_domain');
        $user->photo = $storage_domain . '/' . $user->photo;
        return $user;
    }

    public function updateUser(Request $request)
    {

        $id = Auth::id();

        try {
            DB::beginTransaction();
            $user = User::where('id', $id)->get()->first();
            $user->name = $request->name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            $user->biography = $request->biography;
            $user->date_birth = $request->date_birth;
            $user->id_country = $request->id_country;
            $user->city = $request->city;

            if ($user->update()) {
                $response['status'] = 'ok';
            } else {
                $response['status'] = 'error';
            }
            echo json_encode($response);
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function getCountries()
    {
        $country = Country::select('id', 'name')->get();
        return $country;
    }

    public function photoDefault()
    {
        $photoDefault = DB::table('options')->where('description', '=', 'default_avatar')->get();
        if (count($photoDefault) != 0) {
            $photoDefault = Storage::disk('s3')->exists('images/' . $photoDefault[0]->value) ? 'images/' . $photoDefault[0]->value : '';
        } else {
            $photoDefault = '';
        }
        return $photoDefault;
    }

    //actualizar la fecha de vencimiento de la membresía del usuario
    public function updateUserMembershipExpirationDate($user_id, $account_type_id)
    {

        $accountTypeDetail = AccountTypeDetail::where('user_id', $user_id)->first();
        return $this->saveUserMembershipHistory($accountTypeDetail, $account_type_id);
    }

    //guardar la fecha de vencimiento de la membresía del usuario
    public function saveUserMembershipExpirationDate($user_id, $account_type_id)
    {

        $accountTypeDetail = new AccountTypeDetail();
        $accountTypeDetail->user_id = $user_id;
        return $this->saveUserMembershipHistory($accountTypeDetail, $account_type_id);
    }

    public function saveUserMembershipHistory($accountTypeDetail, $account_type_id)
    {

        $date = date("Y-m-d H:i:s");
        $account_type = AccountType::find($account_type_id);
        $accountTypeDetail->purchase_date = $date;

        if ($accountTypeDetail->id) { //actualizar
            //Obtener el anterior tipo
            $accountTypeDetailHis = AccountTypeDetailHistory::where(['account_type_detail_id' => $accountTypeDetail->id, 'status' => 1])->first();

            if (($accountTypeDetail->expiration_date <= $date) or ($accountTypeDetailHis->account_type_id != $account_type_id)) {
                $accountTypeDetail->expiration_date = date("Y-m-d H:i:s", strtotime($date . "+" . $account_type->enrollment_duration . " month"));
            } else { //Agregar meses a su fecha de expiracion si esta aun no a terminado y si el tipo de memebresia es igual a la anterior suscripcion
                $accountTypeDetail->expiration_date = date("Y-m-d H:i:s", strtotime($accountTypeDetail->expiration_date . "+" . $account_type->enrollment_duration . " month"));
            }

            //actualizar el estado de la anterior suscripcion
            $accountTypeDetailHis->status = false;
            $accountTypeDetailHis->save();
        } else {
            $accountTypeDetail->expiration_date = date("Y-m-d H:i:s", strtotime($date . "+" . $account_type->enrollment_duration . " month"));
        }

        $accountTypeDetail->status = true;

        //guardar o actualizar un registro principal de la suscripcion
        if ($accountTypeDetail->save()) {
            //agregar un nuevo registro de las suscripciones del usuario
            $accountTypeDetailHistory = new AccountTypeDetailHistory();
            $accountTypeDetailHistory->account_type_id = $account_type_id;
            $accountTypeDetailHistory->account_type_detail_id = $accountTypeDetail->id;
            $accountTypeDetailHistory->purchase_date = $accountTypeDetail->purchase_date;
            $accountTypeDetailHistory->expiration_date = $accountTypeDetail->expiration_date;
            $accountTypeDetailHistory->status = $accountTypeDetail->status;
            $accountTypeDetailHistory->save();
        }
        return $accountTypeDetail;
    }

    public function getRolename()
    {
        $user = User::findOrFail(auth()->user()->id);
        $role = $user->getRoleNames()->first();
        return $this->responseOk('', $role);
    }

    public function listUserRoleRequest()
    {
        $users = User::join('role_requests', 'users.id', '=', 'role_requests.id_user')
            ->where('role_requests.status', 1)
            ->select('users.*', 'role_requests.*')
            ->get();
        return $this->responseOk('', $users);
    }

    public function listUserRoleToolRequest()
    {
        $users = User::join('tool_permission_requests', 'users.id', '=', 'tool_permission_requests.id_user')
            ->where('tool_permission_requests.status', 1)
            ->select('users.*', 'tool_permission_requests.*')
            ->get();
        return $this->responseOk('', $users);
    }

    public function conditions()
    {
        return view('conditions');
    }

    public function changePassword(Request $request)
    {
        $user = User::findOrFail(auth()->user()->id);
        if (Hash::check($request->actual_pass, $user->password)) {
            $user->password = Hash::make($request->new_pass);
            $user->update();
            return "Cambio de contraseña exitoso";
        } else {
            return "Ingrese su contraseña actual correctamente";
        }
    }

    public function verifyUniqueEmail(Request $request)
    {
        $isUsed = User::where('email', $request->new_email)->exists();
        return $isUsed;
    }

    public function sendRecoveryEmail(Request $request)
    {
        $user = User::where('email', $request->email)
            ->select('username', 'id', 'password', 'email')
            ->first();

        $i = 0;
        do {
            $rand = rand(10000, 99999);

            if (!User::where('recovery_code', $rand)->exists()) {
                $user->recovery_code = $rand;
                $user->recovery_attempts = 0;
                $user->update();
                $i = 1;
            }
        } while ($i == 0);

        $data = (object) array('username' => $user->username, 'password' => $rand);
        $content = new EmailRecoveryMailable($data);

        Mail::to($user->email)
            ->cc($user->email)
            ->bcc($user->email)
            ->send($content);
    }

    public function recoveryPassword(Request $request)
    {
        $message = "";
        $user = User::where('email', $request->email)->first();
        if ($user == null) {
            $message = "Ingrese su correo correctamente";
        }
        if ($user->recovery_attempts > 3) {
            $user->recovery_code = null;
            $message = "Ha superado la cantidad de intentos permitidos";
        } else {
            if ($request->code == $user->recovery_code) {
                $user->recovery_code = null;
                $user->recovery_attempts = null;
                $user->password = Hash::make($request->password);
                $message = "Contraseña actualizada satisfactoriamente";
            } else {
                $attempts = (int) $user->recovery_attempts;
                $user->recovery_attempts = $attempts + 1;
                $message = "El código ingresado no es correcto";
            }
        }
        $user->update();
        return $message;
    }

    public function prueba()
    {
        $course_id = 1;
        $user_id = 2;

        Browsershot::url("http://promolider.test/get-certificado?course_id=$course_id&user_id=$user_id")
            ->setOption('landscape', true)
            ->windowSize(1100, 580)
            ->waitUntilNetworkIdle()
            ->save('google.jpg');
        $path = 'certificates';
        $name = '/certificado_prueba';
        $file = 'google.jpg';
        Storage::disk('s3')->put($path . $name, file_get_contents($file), 'public');
    }

    public function getCertificado(Request $request)
    {
        $course = Course::find($request->course_id);
        $user = User::find($request->user_id);
        $plantillaProductor = UserConfiguration::select('id', 'value')
            ->where(['user_id' => $course->user_id, 'configuration_id' => 1])
            ->first();
        //al parecer el error es q no se configura adecuadamente el id del productor en la tabla
        $signatureProductor = UserConfiguration::select('id', 'value', )
            ->where(['user_id' => $course->user_id, 'configuration_id' => 2])
            ->first();
        //id creador del la plantilla (admin)
        $certificate = Certificates::find($plantillaProductor->value);
        $user_productor = User::find($course->user_id);
        //Admin creador de la plantilla, info
        $user_admin = User::find($certificate->id_user);
        $signatureAdmin = UserConfiguration::select('id', 'value', )
            ->where(['user_id' => $certificate->id_user, 'configuration_id' => 2])
            ->first();

        $img_admin = '<img crossorigin="anonymous" class="signatureImg"  src="https://promolider-storage-user.s3-accelerate.amazonaws.com/' . ($signatureAdmin->value) . '"height="50px"/>';
        $img = '<img crossorigin="anonymous" class="signatureImg" src="https://promolider-storage-user.s3-accelerate.amazonaws.com/' . ($signatureProductor->value) . '"height="50px"/>';

        $usuario = $user->name . " " . $user->last_name;
        $curso = $course->title;
        $firma_administrador = $img_admin;
        $administrador = $user_admin->name;
        $firma_productor = $img;
        $productor = $user_productor->name;

        return view('content.certificado', compact('usuario', 'curso', 'firma_administrador', 'administrador', 'firma_productor', 'productor'));
    }

    public function preview(Request $request)
    {
        $user = auth()->user();
        $product = Course::join('modules', 'courses.id', '=', 'modules.id_courses')
            ->join('class', 'modules.id', '=', 'class.id_modules')
            ->where('courses.slug', $request->slug_product)
            ->select('class.slug', 'courses.id')
            ->first();

        $slug = $product == null ? 'No hay clases' : $product->slug;
        $destination = 'preview';
        $data = [$user->username, $user->password, $destination, $request->slug_product, $slug, $product->id];
        $data = json_encode($data);
        $data = urlencode($data);
        return $data;
    }

    public function getOpcStatus()
    {
        try {
            $user = auth()->user();

            $product = Product::where('account_type_id', $user->id_account_type)
                ->where('name', 'opc')
                ->first();

            if (!$product) {
                return response()->json([
                    'opc_status' => 'not_found'
                ]);
            }

            return response()->json([
                'opc_status' => $product->price == 0 ? 'indefinite' : 'finite'
            ]);

        } catch (\Exception $e) {
            Log::error('Error en getOpcStatus: ' . $e->getMessage());
            return response()->json([
                'opc_status' => 'error'
            ], 500);
        }
    }

    private function sendMembershipReceipt(
        $userId, 
        $membershipId, 
        $totalPaid, 
        $basePrice, 
        $ivaAmount, 
        $ivaPercentage, 
        $paymentMethod = 'OpenPay',
        $paymentReference = null
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

            $receiptNumber = $this->generateReceiptNumber();
            $activationDate = now();
            $expirationDate = Carbon::parse($user->expiration_membership_date);
            
            // Calcular duración usando enrollment_duration de la tabla account_type
            $enrollmentMonths = $membership->enrollment_duration ?? 12;
            $daysInDuration = $enrollmentMonths * 30;
            
            if ($enrollmentMonths == 12) {
                $membershipDuration = "1 año (365 días)";
            } elseif ($enrollmentMonths == 6) {
                $membershipDuration = "6 meses (180 días)";
            } elseif ($enrollmentMonths == 1) {
                $membershipDuration = "1 mes (30 días)";
            } else {
                $membershipDuration = "{$enrollmentMonths} meses ({$daysInDuration} días)";
            }
            
            $membershipBenefits = $this->getMembershipBenefits($membership);
            
            // Nombre de la membresía (usar 'account' en lugar de 'name')
            $membershipName = $membership->account ?? 'Membresía';
            
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
                'payment_reference' => $paymentReference,
                'email_sent_to' => $user->email,
                'email_sent_at' => now(),
                'email_status' => 'sent',
                'course_title' => null,
                'instructor_name' => null,
                'user_name' => $user->name . ' ' . $user->last_name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

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
        }
    }

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
        
        $benefits[] = "• Acceso a todas las funcionalidades de la plataforma";
        $benefits[] = "• Soporte prioritario";
        
        return implode('<br>', $benefits);
    }

    private function generateReceiptNumber()
    {
        $lastReceipt = DB::table('payment_receipts')->orderBy('id', 'desc')->first();
        
        if ($lastReceipt) {
            $lastNumber = intval($lastReceipt->receipt_number);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 100;
        }
        
        return str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }
    
}
