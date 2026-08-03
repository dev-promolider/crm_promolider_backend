<?php
namespace Promolider\Infrastructure\Registration\In\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Promolider\Application\Registration\UseCases\CreatePreregistroUseCase;
use Promolider\Application\Registration\UseCases\ValidatePreregistroTokenUseCase;
use Promolider\Application\Registration\UseCases\ProcessOpenpayRegistrationUseCase;
use Promolider\Application\Registration\UseCases\CheckDuplicateUseCase;
use Promolider\Application\Registration\UseCases\GetPreregistroConfigUseCase;
use Promolider\Application\Registration\UseCases\SendRadarEventUseCase;
use Promolider\Application\Registration\UseCases\SavePreregistroConfigUseCase;
use Promolider\Application\Registration\UseCases\GetPreregistroReferralsUseCase;
use Promolider\Application\Registration\UseCases\ResendPreregistroLinkUseCase;
use Promolider\Application\Registration\UseCases\CheckPreregistroPaymentStatusUseCase;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Exception;

class PreregistroController extends Controller
{
    public function __construct(
        private CreatePreregistroUseCase $createPreregistroUseCase,
        private ValidatePreregistroTokenUseCase $validatePreregistroTokenUseCase,
        private ProcessOpenpayRegistrationUseCase $processOpenpayRegistrationUseCase,
        private CheckDuplicateUseCase $checkDuplicateUseCase,
        private GetPreregistroConfigUseCase $getPreregistroConfigUseCase,
        private SendRadarEventUseCase $sendRadarEventUseCase,
        private SavePreregistroConfigUseCase $savePreregistroConfigUseCase,
        private GetPreregistroReferralsUseCase $getPreregistroReferralsUseCase,
        private ResendPreregistroLinkUseCase $resendPreregistroLinkUseCase,
        private CheckPreregistroPaymentStatusUseCase $checkPreregistroPaymentStatusUseCase
    ) {}

    /**
     * POST /registration/preregistro/{username}
     * Crea un nuevo preregistro.
     */
    public function store(Request $request, string $username)
    {
        $request->validate([
            'nombres'           => 'required|string|max:100',
            'apellidos'         => 'required|string|max:100',
            'correo'            => 'required|email',
            'whatsapp'          => 'required|string|max:20',
            'referrer_nombre'   => 'nullable|string|max:100',
            'referrer_apellido' => 'nullable|string|max:100',
            'referrer_correo'   => 'nullable|email|max:255',
            'referrer_whatsapp' => 'nullable|string|max:20',
            'url_invitacion'    => 'nullable|string|max:500',
        ], [
            'nombres.required'  => 'El nombre es obligatorio.',
            'apellidos.required' => 'El apellido es obligatorio.',
            'correo.required'   => 'El correo electrónico es obligatorio.',
            'correo.email'      => 'El correo ingresado no tiene un formato válido.',
            'whatsapp.required' => 'El número de WhatsApp es obligatorio.',
        ]);

        $result = $this->createPreregistroUseCase->execute($username, $request->all());

        return response()->json($result, $result['status'] === 'created' ? 201 : 200);
    }

    /**
     * GET /registration/preregistro/retorno/{token}
     * Valida un token de preregistro.
     */
    public function retorno(string $token)
    {
        try {
            $data = $this->validatePreregistroTokenUseCase->execute($token);

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (Exception $e) {
            $code = is_numeric($e->getCode()) && $e->getCode() >= 100 && $e->getCode() <= 599
                ? $e->getCode()
                : 500;

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $code);
        }
    }

    /**
     * POST /registration/preregistro/openpay
     * Procesa el registro con pago Openpay.
     */
    public function openpay(Request $request, \Promolider\Domain\Registration\Ports\Out\PreregistroRepositoryInterface $preregistroRepository)
    {
        $pendingUnverifiedId = $preregistroRepository->getPendingUnverifiedId(
            $request->input('correo', ''),
            $request->input('preregistro_id')
        );

        $uniquePendingUsername = Rule::unique('unverified_users', 'username');
        if ($pendingUnverifiedId) {
            $uniquePendingUsername->ignore($pendingUnverifiedId);
        }

        try {
            $data = $request->validate([
                'usuario'           => ['required', 'string', 'min:4', 'max:50', 'regex:/^[A-Za-z0-9_]+$/', Rule::unique('users', 'username'), $uniquePendingUsername],
                'correo'            => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
                'password'          => 'required|string|min:6',
                'password_confirm'  => 'required|string|same:password',
                'tipo_usuario'      => 'required|string|max:50',
                'nombre'            => ['required', 'string', 'max:255', 'regex:/^[\pL\s\'\-]+$/u'],
                'apellido'          => ['required', 'string', 'max:255', 'regex:/^[\pL\s\'\-]+$/u'],
                'telefono'          => ['required', 'string', 'max:20', 'regex:/^[0-9]{7,15}$/', Rule::unique('users', 'phone')],
                'fecha_nacimiento'  => 'required|date|before:-18 years',
                'tipo_documento'    => 'required|string|max:50',
                'numero_documento'  => ['required', 'string', 'max:50', Rule::unique('users', 'nro_document')],
                'pais'              => 'nullable|string|max:100',
                'tipo_cuenta'       => 'required|string|max:50',
                'metodo_pago'       => 'required|string|max:50',
                'referidor'         => 'required|string|max:255',
                'lado'              => 'required|in:izquierda,derecha',
                'preregistro_id'    => 'nullable|integer|exists:preregistros,id',
            ], [
                'usuario.required'         => 'El nombre de usuario es obligatorio.',
                'usuario.min'              => 'El usuario debe tener al menos 4 caracteres.',
                'usuario.regex'            => 'El usuario solo puede contener letras, números y guion bajo (_).',
                'usuario.unique'           => 'Este nombre de usuario ya está en uso.',
                'correo.required'          => 'El correo electrónico es obligatorio.',
                'correo.email'             => 'El correo ingresado no es válido.',
                'correo.unique'            => 'Este correo ya está registrado.',
                'password.required'        => 'La contraseña es obligatoria.',
                'password.min'             => 'La contraseña debe tener al menos 6 caracteres.',
                'password_confirm.same'    => 'Las contraseñas no coinciden.',
                'telefono.required'        => 'El número de teléfono es obligatorio.',
                'telefono.regex'           => 'El teléfono debe contener solo dígitos (entre 7 y 15 números).',
                'telefono.unique'          => 'Este número de teléfono ya está registrado.',
                'fecha_nacimiento.before'  => 'Debes tener al menos 18 años para registrarte.',
                'numero_documento.unique'  => 'Este número de documento ya está registrado.',
                'lado.required'            => 'No se detectó el lado de registro.',
                'referidor.required'       => 'No se encontró el usuario que te invitó.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Illuminate\Support\Facades\Log::error('Openpay Validation Failed', $e->errors());
            throw $e;
        }

        // Validación adicional del número de documento según tipo
        $this->validateDocumentNumber($request);

        try {
            $result = $this->processOpenpayRegistrationUseCase->execute($data);

            Log::info('Cargo Openpay de preregistro creado', [
                'charge_id' => $result['charge_id'],
                'correo'    => $data['correo'],
            ]);

            return response()->json($result);

        } catch (Exception $e) {
            if ($e->getCode() === 422) {
                throw ValidationException::withMessages([
                    'referidor' => $e->getMessage(),
                ]);
            }
            throw $e;

        } catch (\Openpay\Data\OpenpayApiTransactionError $e) {
            Log::error('Error de transacción Openpay (preregistro)', [
                'message' => $e->getMessage(),
                'error_code' => $e->getErrorCode(),
            ]);

            return response()->json([
                'message' => $this->humanizeOpenpayError($e->getErrorCode(), $e->getMessage()),
            ], 422);

        } catch (\Openpay\Data\OpenpayApiConnectionError $e) {
            Log::error('Error de conexión Openpay', ['message' => $e->getMessage()]);

            return response()->json([
                'message' => 'No pudimos conectar con la pasarela de pago. Intenta en unos minutos.',
            ], 503);

        } catch (\Throwable $exception) {
            Log::error('Error inesperado en registro Openpay', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Ocurrió un error inesperado. Intenta de nuevo o contacta al soporte.',
            ], 500);
        }
    }

    /**
     * POST /registration/preregistro/webhook/openpay
     */
    public function openpayWebhook(Request $request, \Promolider\Application\Registration\UseCases\ProcessOpenpayWebhookUseCase $webhookUseCase)
    {
        // CRM-25: Verificar firma HMAC de Openpay si la clave y encabezado existen
        $sk = config('services.openpay.sk', env('OPENPAY_SK'));
        $signatureHeader = $request->header('X-Openpay-Signature') ?? $request->header('X-OpenPay-Webhook-Signature');

        if (!empty($sk) && !empty($signatureHeader)) {
            $expected = hash_hmac('sha256', $request->getContent(), $sk);
            if (!hash_equals($expected, $signatureHeader)) {
                Log::warning('Firma HMAC inválida en webhook de Openpay', ['ip' => $request->ip()]);
                return response()->json(['error' => 'Firma inválida'], 401);
            }
        }

        try {
            $webhookUseCase->execute($request->all());
            return response()->json(['success' => 'success'], 200);
        } catch (\Throwable $e) {
            Log::error('Error procesando webhook de openpay', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'internal error'], 500);
        }
    }

    /**
     * GET /registration/preregistro/check-duplicate
     */
    public function checkDuplicate(Request $request)
    {
        $request->validate([
            'field' => 'required|string',
            'value' => 'required|string',
        ]);

        $result = $this->checkDuplicateUseCase->execute(
            $request->input('field'),
            $request->input('value')
        );

        return response()->json($result);
    }

    /**
     * GET /registration/preregistro/config/{username}
     */
    public function config(string $username)
    {
        try {
            $config = $this->getPreregistroConfigUseCase->execute($username);

            return response()->json([
                'success' => true,
                'data' => $config,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * POST /registration/preregistro/config
     */
    public function saveConfig(Request $request)
    {
        $data = $request->validate([
            'lado' => 'required|in:izquierda,derecha',
            'landing' => 'required|in:claro,oscuro',
        ]);

        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'No autorizado'], 401);
        }

        $link = $this->savePreregistroConfigUseCase->execute(
            $user->username,
            $data['lado'],
            $data['landing']
        );

        return response()->json([
            'ok' => true,
            'config' => $link,
        ]);
    }

    /**
     * GET /registration/preregistro/referrals
     */
    public function referrals(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'No autorizado'], 401);
        }

        $result = $this->getPreregistroReferralsUseCase->execute($user->username, $user->id);

        return response()->json($result);
    }

    /**
     * POST /registration/preregistro/resend-link
     */
    public function resendLink(Request $request)
    {
        $request->validate([
            'correo' => 'required|email',
        ]);

        $result = $this->resendPreregistroLinkUseCase->execute($request->input('correo'));

        $status = $result['ok'] === false ? 409 : 200;
        return response()->json($result, $status);
    }

    /**
     * GET /registration/preregistro/check-payment/{email}
     */
    public function checkPaymentStatus(string $email)
    {
        $result = $this->checkPreregistroPaymentStatusUseCase->execute($email);
        
        $status = $result['status'] === 'not_found' ? 404 : 200;
        return response()->json($result, $status);
    }

    /**
     * POST /registration/preregistro/radar
     */
    public function radar(Request $request)
    {
        $this->sendRadarEventUseCase->execute($request->all());

        return response()->json(['ok' => true]);
    }

    // ─── Helpers privados (infraestructura) ──────────────────────────────────

    private function validateDocumentNumber(Request $request): void
    {
        $tipo   = $request->input('tipo_documento');
        $numero = $request->input('numero_documento', '');
        $error  = null;

        switch (strtolower(trim($tipo))) {
            case 'dni':
                if (!preg_match('/^[0-9]{8}$/', $numero)) {
                    $error = 'El DNI debe tener exactamente 8 dígitos numéricos.';
                }
                break;
            case 'carnet_extranjeria':
                if (!preg_match('/^[A-Za-z0-9]{6,12}$/', $numero)) {
                    $error = 'El Carnet de Extranjería debe tener entre 6 y 12 caracteres alfanuméricos.';
                }
                break;
            case 'pasaporte':
                if (!preg_match('/^[A-Za-z0-9]{6,20}$/', $numero)) {
                    $error = 'El pasaporte debe tener entre 6 y 20 caracteres alfanuméricos.';
                }
                break;
        }

        if ($error) {
            throw ValidationException::withMessages(['numero_documento' => $error]);
        }
    }

    private function humanizeOpenpayError(?string $errorCode, string $fallback): string
    {
        $messages = [
            '3001' => 'La tarjeta fue rechazada por el banco.',
            '3002' => 'La tarjeta ha expirado.',
            '3003' => 'La tarjeta no tiene fondos suficientes.',
            '3004' => 'La tarjeta fue reportada como robada.',
            '3005' => 'La tarjeta fue rechazada por el sistema antifraude.',
            '3008' => 'La tarjeta no está habilitada para compras por internet.',
            '2004' => 'El número de tarjeta ingresado no es válido.',
            '2005' => 'La tarjeta está vencida.',
            '2006' => 'El código de seguridad (CVV) es incorrecto.',
            '2009' => 'El código de seguridad (CVV) ingresado es incorrecto.',
            '2010' => 'La autenticación 3D Secure falló.',
        ];

        return $messages[$errorCode] ?? 'El pago fue rechazado: ' . $fallback;
    }
}
