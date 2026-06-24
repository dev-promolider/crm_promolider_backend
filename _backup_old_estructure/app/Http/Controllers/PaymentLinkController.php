<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\UnverifiedUser;
use App\Traits\ResponseFormat;
use App\Models\UnverifiedPayment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\PayController;
use App\Http\Resources\PaymentResource;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\TransactionController;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Models\User;
use App\Models\Course;
use App\Models\Category;
use App\Models\Module;
use App\Models\Clas;
use App\Models\AccountType;
use App\Services\PHPMailerService;

class PaymentLinkController extends Controller 
{
    use ResponseFormat;
    
    public function __construct(){
        $this->middleware('can:withdrawal_funds')->only('listMyPayments');
        $this->middleware('can:payment')->only('index');
    }
    public function index()
    {
        return view('content.payment.payment');
    }

    public function List(Request $request): AnonymousResourceCollection
    {
        $payments = Payment::query()->with(['paymentMethod', 'user'])->get();
        return PaymentResource::collection($payments);
    }

    public function getAll(){
        $payments = Payment::all('created_at', 'amount', 'operation_number as reason');
        return $payments;
    }

    public function getAllUser($id)
    {
        try {
            // Validación usando Policy
            $this->authorize('viewPayments', [User::class, $id]);
            
            $payments = Payment::query()->where('user_id', $id)->with(['paymentMethod', 'user'])->get();
            return PaymentResource::collection($payments);
            
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para acceder a esta información'
            ], 403);
        }
    }

    public function listUserPayments()
    {
        $payments = Payment::paymentAuthSponsor()->with(['user.accountType', 'products'])->get();
        return PaymentResource::collection($payments);
    }

    public function listMyPayments()
    {
        return view('content.requests.payments');
    }

    public function openpayWebhookConfirm(Request $request)
    {
        Log::info('Webhook Openpay recibido', ['type' => $request->type]);

        // ─── Verificación Basic Auth ────────────────────────────────────────
        if (!$this->verifyOpenpayWebhookSignature($request)) {
            Log::warning('Webhook Openpay: credenciales Basic Auth inválidas');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        if ($request->type === 'verification') {
            Log::info('Webhook Openpay: verification');
            return response()->json(['success' => 'success'], 200);
        }
    
        Log::info('Webhook Openpay: transaction', [
            'transaction_id' => $request->transaction['id'] ?? null,
            'status' => $request->transaction['status'] ?? null
        ]);
    
        if (!$request->has('transaction') || !is_array($request->transaction)) {
            Log::info('Webhook Openpay: payload sin transaction');
            return response()->json(['success' => 'success'], 200);
        }
        $transaction = $request->transaction;
        if (!isset($transaction['status'])) {
            return response()->json(['success' => 'success'], 200);
        }
        if ($transaction['status'] == 'completed') {
            Log::info('Transacción completada, guardando en tabla transactions', ['transaction' => $request->transaction]);
        
            // Guardar la transacción
            app(TransactionController::class)->createTransaction($request->transaction);
        
            // Verificar si es un UnverifiedUser
            if (UnverifiedUser::where('openpay_order_id', $request->transaction['id'])->exists()) {
                Log::info('Se encontró un UnverifiedUser', ['order_id' => $request->transaction['id']]);
            
                $user = UnverifiedUser::where('openpay_order_id', $request->transaction['id'])->first();
                $data2 = json_decode($user->data, true);
            
                Log::info('Datos del usuario recuperados de UnverifiedUser', ['data' => $data2]);
            
                session()->forget("body");
                session(['body' => $data2]);
                session(['encrypted_pass' => true]);
            
                try {
                    Log::info('Llamando a UserController::Create', ['order_id' => $user->openpay_order_id]);
                    app(UserController::class)->Create($user->openpay_order_id);
                    Log::info('Usuario creado con éxito', ['order_id' => $user->openpay_order_id]);
                    
                    // ✅ NOTIFICAR A N8N — Caso: Nuevo usuario creado exitosamente
                    $this->notifyN8N(
                        ($data2['name'] ?? '') . ' ' . ($data2['last_name'] ?? ''),
                        $data2['email'] ?? '',
                        'pagado'
                    );

                    $user->delete();
                    Log::info('Registro en UnverifiedUser eliminado', ['order_id' => $user->openpay_order_id]);
                } catch (\Throwable $e) {
                    Log::error('Error creando usuario', [
                        'order_id' => $user->openpay_order_id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            
            // Verificar si es un UnverifiedPayment
            } else if (UnverifiedPayment::where('openpay_order_id', $request->transaction['id'])->exists()) {
                $payment = UnverifiedPayment::where('openpay_order_id', $request->transaction['id'])->first();
                Log::info('Se encontró un UnverifiedPayment', ['payment' => $payment]);
            
                try {
                    if ($payment->product_name == 'opc') {
                        app(UserController::class)->recompraUpdate($payment->openpay_order_id);
                    } else if ($payment->product_name == 'membership') {
                        app(UserController::class)->membershipUpdate($payment->openpay_order_id, $payment->product_id);
                    } else if ($payment->product_name == 'course') {
                        $req = new Request();
                        $req->merge([
                            'id_course' => $payment->product_id,
                            'user_id' => $payment->user_id,
                            'type_purchase' => 1
                        ]);
                        app(CartController::class)->buyCourse($req);
                        app(PayController::class)->savePaymentRecharge(
                            $payment->user_id,
                            $payment->product_price,
                            $payment->openpay_order_id,
                            1,
                            $payment->product_detail
                        );
                        
                        // 💳 ENVIAR COMPROBANTE DE PAGO - Pago con OpenPay
                        try {
                            $this->sendPaymentReceipt(
                                $payment->product_id,
                                $payment->user_id,
                                $payment->product_price,
                                'OpenPay',
                                $payment->openpay_order_id
                            );
                        } catch (\Exception $e) {
                            Log::error('Error enviando comprobante de pago (OpenPay): ' . $e->getMessage());
                        }
                    } else if ($payment->product_name == 'recharge_found') {
                        app(PayController::class)->saveOpcWallet(
                            $payment->user_id,
                            $payment->product_price,
                            4,
                            $payment->product_detail
                        );
                        app(PayController::class)->savePaymentRecharge(
                            $payment->user_id,
                            $payment->product_price,
                            $payment->openpay_order_id,
                            1,
                            "Recarga de Fondos"
                        );
                    }
                    
                    
                    $this->notifyN8N(
                        'Usuario Existente',
                        $payment->user_email ?? 'cliente@promolider.org',
                        'pagado'
                    );
                    
                    $payment->delete();
                    Log::info('Registro en UnverifiedPayment eliminado', ['order_id' => $payment->openpay_order_id]);
                } catch (\Throwable $e) {
                    Log::error('Error procesando UnverifiedPayment', [
                        'order_id' => $payment->openpay_order_id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            } else {
                Log::warning('No se encontró UnverifiedUser ni UnverifiedPayment con este order_id', [
                    'order_id' => $request->transaction['id']
                ]);
            }
        } else {
           
            $status = $request->transaction['status'] ?? 'unknown';

            Log::info('Transacción no completada', [
                'transaction_id' => $request->transaction['id'] ?? null,
                'status' => $request->transaction['status'] ?? null
            ]);

            
            $user = UnverifiedUser::where('openpay_order_id', $request->transaction['id'])->first();
            if ($user) {
                $data2 = json_decode($user->data, true);
                $this->notifyN8N(
                    ($data2['name'] ?? '') . ' ' . ($data2['last_name'] ?? ''),
                    $data2['email'] ?? '',
                    $status  // failed / cancelled / expired / etc.
                );
            } else {
                
                $payment = UnverifiedPayment::where('openpay_order_id', $request->transaction['id'])->first();
                if ($payment) {
                    $this->notifyN8N(
                        'Usuario Existente',
                        $payment->user_email ?? 'cliente@promolider.org',
                        $status  // failed / cancelled / expired / etc.
                    );
                }
            }
        }
    }

    public function getOpenpayConditions(){
        return view('modalOpenpayConditions');
    }

    
    private function sendPaymentReceipt($courseId, $userId, $amountPaid, $paymentMethod = 'OpenPay', $transactionId = null)
    {
        try {
            $course = Course::find($courseId);
            $student = User::find($userId);
            
            if (!$course || !$student) {
                Log::warning('No se pudo enviar comprobante: Curso o usuario no encontrado', [
                    'course_id' => $courseId,
                    'user_id' => $userId
                ]);
                return;
            }

            // Obtener datos del curso
            $category = Category::find($course->id_categories);
            $instructor = User::find($course->user_id);
            $totalModules = Module::where('id_courses', $course->id)->count();
            $totalLessons = Clas::whereHas('module', function($query) use ($course) {
                $query->where('id_courses', $course->id);
            })->count();

            // Calcular descuento si aplica
            $accountType = AccountType::find($student->id_account_type);
            $discountPercentage = $accountType ? $accountType->disc_purchases_course : 0;
            $discountAmount = ($course->price * $discountPercentage) / 100;

            // 🆕 GENERAR NÚMERO DE COMPROBANTE CORRELATIVO
            $receiptNumber = $this->generateReceiptNumber();
            
            // Guardar comprobante en la base de datos
            $purchasedCourse = \App\Models\PurchasedCourse::where('user_id', $userId)
                ->where('course_id', $courseId)
                ->orderBy('id', 'desc')
                ->first();
            
            DB::table('payment_receipts')->insert([
                'receipt_number' => $receiptNumber,
                'user_id' => $userId,
                'course_id' => $courseId,
                'purchased_course_id' => $purchasedCourse ? $purchasedCourse->id : null,
                'amount_paid' => $amountPaid,
                'course_price' => $course->price,
                'discount_amount' => $discountAmount,
                'discount_percentage' => $discountPercentage,
                'payment_method' => $paymentMethod,
                'payment_reference' => $transactionId,
                'email_sent_to' => $student->email,
                'email_sent_at' => now(),
                'email_status' => 'sent',
                'course_title' => $course->title,
                'instructor_name' => $instructor ? $instructor->name . ' ' . $instructor->last_name : 'Promolíder',
                'user_name' => $student->name . ' ' . $student->last_name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Usar el número de comprobante como transaction_id
            $transactionId = $receiptNumber;

            // Preparar datos para la plantilla
            $templateData = [
                // Estudiante
                'student_name' => $student->name . ' ' . $student->last_name,
                'student_email' => $student->email,
                
                // Curso
                'course_title' => $course->title,
                'course_image' => $course->url_portada ? "https://vcr.promolider.info/storage/{$course->url_portada}" : '',
                'course_category' => $category ? $category->category : 'General',
                'instructor_name' => $instructor ? $instructor->name . ' ' . $instructor->last_name : 'Promolíder',
                'total_modules' => $totalModules,
                'total_lessons' => $totalLessons,
                'course_duration' => $course->duration ?? 'Acceso ilimitado',
                'course_url' => "https://vcr.promolider.info/course/{$course->id}",
                
                // Pago
                'transaction_id' => $transactionId,
                'payment_date' => now()->format('d/m/Y H:i:s'),
                'payment_method' => $paymentMethod,
                'course_price' => $course->price,
                'discount_percentage' => $discountPercentage,
                'discount_amount' => $discountAmount,
                'amount_paid' => $amountPaid,
                
                // Facturación (opcional)
                'include_billing_info' => false,
                'billing_name' => '',
                'billing_address' => '',
            ];

            $phpMailerService = new PHPMailerService();
            $phpMailerService->sendEmailWithTemplate(
                $student->email,
                'Comprobante de Pago - ' . $course->title,
                'emails.comprobante-pago-curso',
                $templateData
            );

            Log::info('Comprobante de pago enviado exitosamente', [
                'course_id' => $courseId,
                'user_id' => $userId,
                'email' => $student->email,
                'amount' => $amountPaid,
                'transaction_id' => $transactionId,
                'payment_method' => $paymentMethod
            ]);

        } catch (\Exception $e) {
            Log::error('Error enviando comprobante de pago: ' . $e->getMessage(), [
                'course_id' => $courseId,
                'user_id' => $userId,
                'trace' => $e->getTraceAsString()
            ]);
            // No lanzar excepción para no interrumpir el flujo de compra
        }
    }

    /**
     * Genera el número de comprobante correlativo de 5 dígitos
     * Empieza desde 00100 y va incrementando: 00101, 00102, etc.
     */
    private function generateReceiptNumber()
    {
        // Obtener el último número de comprobante
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
    private function notifyN8N(string $nombre, string $email, string $estadoPago): void
    {
        try {
            $url     = 'https://ia.promolider.org/webhook-test/pago_promolider';
            $payload = json_encode([
                'nombre'      => $nombre,
                'email'       => $email,
                'estado_pago' => $estadoPago,
            ]);

            $headers = [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($payload),
            ];

            // ─── Basic Auth (optativo) ──────────────────────────────────────
            // Si N8N_WEBHOOK_USER y N8N_WEBHOOK_PASS están configurados en el
            // .env, se envía autenticación Basic Auth. Si no, se omite.
            $basicUser = config('services.n8n.webhook_user');
            $basicPass = config('services.n8n.webhook_pass');

            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_TIMEOUT        => 10,
            ]);

            // Basic Auth si está configurado
            if ($basicUser && $basicPass) {
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERPWD, $basicUser . ':' . $basicPass);
            }

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            Log::info('Notificación n8n enviada', [
                'estado_pago'      => $estadoPago,
                'http_code'        => $httpCode,
                'auth_type'        => ($basicUser && $basicPass) ? 'basic' : 'none',
            ]);

        } catch (\Throwable $e) {
            Log::error('Error notificando a n8n: ' . $e->getMessage());
        }
    }

    /**
     * Verificar las credenciales HTTP Basic Auth del webhook de Openpay.
     * Openpay envía un header Authorization: Basic con usuario y contraseña
     * que se configuran en el dashboard al crear el webhook.
     */
    private function verifyOpenpayWebhookSignature(Request $request): bool
    {
        $expectedUser = config('services.openpay.webhook_user');
        $expectedPass = config('services.openpay.webhook_pass');

        if (empty($expectedUser) || empty($expectedPass)) {
            Log::warning('OPENPAY_WEBHOOK_USER/PASS no configurado — saltando verificación');
            return true;
        }

        $auth = $request->header('Authorization');

        if (empty($auth) || !str_starts_with($auth, 'Basic ')) {
            Log::warning('Webhook Openpay recibido sin header Authorization');
            return false;
        }

        $encoded = substr($auth, 6);
        $decoded = base64_decode($encoded, true);

        if ($decoded === false || !str_contains($decoded, ':')) {
            Log::warning('Webhook Openpay: header Authorization con formato inválido');
            return false;
        }

        [$user, $pass] = explode(':', $decoded, 2);

        $userOk = hash_equals($expectedUser, $user);
        $passOk = hash_equals($expectedPass, $pass);

        if (!$userOk || !$passOk) {
            Log::warning('Webhook Openpay: credenciales Basic Auth inválidas');
            return false;
        }

        return true;
    }

}
