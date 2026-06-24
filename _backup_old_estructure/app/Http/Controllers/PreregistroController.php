<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Schema;
use App\Models\AccountType;
use App\Models\Country;
use App\Models\DocumentType;
use App\Models\Preregistro;
use App\Models\UnverifiedUser;
use App\Models\User;
use App\Models\PreregistroLink;

class PreregistroController extends Controller
{
    // Mostrar formulario
    public function index($username)
    {
        $linkConfig = PreregistroLink::where('username', $username);

        if (Schema::hasColumn('preregistro_links', 'is_active')) {
            $linkConfig->where('is_active', true);
        }

        $linkConfig = $linkConfig->first();

        if (! $linkConfig) {
            abort(404, 'Link de preregistro no configurado o desactivado.');
        }

        $user = User::where('username', $username)->first();

        if (! $user) {
            abort(404, 'Usuario no encontrado.');
        }

        // Usar query param ?tema= si viene, sino lo configurado en DB
        $tema = request('tema', $linkConfig->landing);
        if (! in_array($tema, ['claro', 'oscuro'])) {
            $tema = $linkConfig->landing;
        }

        $view = 'preregistro.landings.' . $tema;

        return view($view, [
            'username' => $username,
            'lado'     => request('lado', $linkConfig->lado),
            'landing'  => $tema,

            'nombre_referidor'   => $user->name,
            'apellido_referidor' => $user->last_name,
            'correo_referidor'   => $user->email,
            'telefono_referidor' => $user->phone,
        ]);
    }


    public function store(Request $request, $username)
    {
        $request->validate([
            'nombres'   => 'required|string|max:100',
            'apellidos' => 'required|string|max:100',
            'correo'    => 'required|email',
            'whatsapp'  => 'required|string|max:20',
            'lado'      => 'required|in:izquierda,derecha',
        ], [
            'nombres.required'   => 'El nombre es obligatorio.',
            'apellidos.required' => 'El apellido es obligatorio.',
            'correo.required'    => 'El correo electrónico es obligatorio.',
            'correo.email'       => 'El correo ingresado no tiene un formato válido.',
            'correo.unique'      => 'Este correo ya fue registrado anteriormente. Si ya tienes un preregistro, usa el enlace que recibiste.',
            'whatsapp.required'  => 'El número de WhatsApp es obligatorio.',
            'lado.required'      => 'No se detectó el lado de registro. Vuelve al enlace original de invitación.',
            'lado.in'            => 'El lado de registro no es válido.',
        ]);

        $correo = $request->correo;

        $linkConfig = PreregistroLink::where('username', $username);

        if (Schema::hasColumn('preregistro_links', 'is_active')) {
            $linkConfig->where('is_active', true);
        }

        $linkConfig = $linkConfig->first();

        if (! $linkConfig) {
            return response()->json([
                'message' => 'Configuración de preregistro no encontrada o desactivada.'
            ], 404);
        }

        $lado = $request->input('lado');

        // ─────────────────────────────────────────────
        // 1. ¿Ya existe como usuario real?
        // ─────────────────────────────────────────────
        $user = User::where('email', $correo)->first();

        if ($user) {
            return response()->json([
                'redirect_url' => url('/login'),
                'status' => 'user_exists'
            ]);
        }

        // ─────────────────────────────────────────────
        // 2. ¿Existe preregistro?
        // ─────────────────────────────────────────────
        $existingPreregistro = Preregistro::where('correo', $correo)->first();

        if ($existingPreregistro) {

            // ─────────────────────────────────────────
            // 3. ¿Tiene pago pendiente?
            // ─────────────────────────────────────────
            $unverified = $this->findPendingPreregistroUser($correo, $existingPreregistro->id);

            // Si tiene pago pendiente
            if ($unverified) {

                return response()->json([
                    'redirect_url' => url('/mi-dashboard') . '#/pago',
                    'preregistro_id' => $existingPreregistro->id,
                    'username' => $username,
                    'lado' => $lado,
                    'status' => 'payment_pending',
                    'preregistro_prefill' => $this->buildPendingRegistrationPrefill($unverified, $existingPreregistro, $username, $lado),
                ]);
            }

            // Tiene preregistro normal
            return response()->json([
                'redirect_url' => url('/mi-dashboard') . '#/',
                'preregistro_id' => $existingPreregistro->id,
                'username' => $username,
                'lado' => $lado,
                'status' => 'already_registered'
            ]);
        }

        $preregistro = Preregistro::create([
            'nombres'           => $request->nombres,
            'apellidos'         => $request->apellidos,
            'correo'            => $request->correo,
            'whatsapp'          => $request->whatsapp,
            'referrer_username' => $username,
            'lado'              => $lado,
        ]);

        $payload = [
            'username'        => $username,
            'lado'            => $lado,
            'preregistro_id'  => $preregistro->id,
            'nombres'         => $preregistro->nombres,
            'apellidos'       => $preregistro->apellidos,
            'correo'          => $preregistro->correo,
            'whatsapp'        => $preregistro->whatsapp,
            'created_at'      => optional($preregistro->created_at)->format(DATE_ATOM),
        ];

        try {
            $basicUser = config('services.n8n.webhook_user');
            $basicPass = config('services.n8n.webhook_pass');

            $httpClient = Http::timeout(15)
                ->acceptJson();

            // ─── Basic Auth (optativo) ──────────────────────────────────────
            // Si N8N_WEBHOOK_USER y N8N_WEBHOOK_PASS están configurados en el
            // .env, se envía autenticación Basic Auth. Si no, se omite.
            if ($basicUser && $basicPass) {
                $httpClient->withBasicAuth($basicUser, $basicPass);
            }

            $httpClient->post('https://ia.promolider.org/webhook-test/registro-promolider', $payload)
                ->throw();
        } catch (\Throwable $exception) {
            Log::error('Error enviando preregistro a n8n', [
                'message' => $exception->getMessage(),
                'payload' => $payload,
            ]);
        }

        return response()->json([
            'ok'             => true,
            'preregistro_id' => $preregistro->id,
            'redirect_url'   => url('/mi-dashboard') . '#/',
            'username'       => $username,
            'lado'           => $lado,
        ]);
    }


    public function openpay(Request $request)
    {
        $pendingUnverified = $this->findPendingPreregistroUser(
            $request->input('correo'),
            $request->input('preregistro_id')
        );
        $uniquePendingUsername = Rule::unique('unverified_users', 'username');

        if ($pendingUnverified) {
            $uniquePendingUsername->ignore($pendingUnverified->id);
        }

        // ─── Validación con mensajes específicos ─────────────────────────────
        $data = $request->validate([
            'usuario' => [
                'required', 'string', 'min:4', 'max:50',
                'regex:/^[A-Za-z0-9_]+$/',
                Rule::unique('users', 'username'),
                $uniquePendingUsername,
            ],
            'correo' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email'),
            ],
            'password'         => 'required|string|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/',
            'password_confirm' => 'required|string|same:password',
            'tipo_usuario'     => 'required|string|max:50',
            'nombre'           => ['required', 'string', 'max:255', 'regex:/^[\pL\s\'\-]+$/u'],
            'apellido'         => ['required', 'string', 'max:255', 'regex:/^[\pL\s\'\-]+$/u'],
            'telefono'         => [
                'required', 'string', 'max:20',
                'regex:/^[0-9]{7,15}$/',
                Rule::unique('users', 'phone'),
            ],
            'fecha_nacimiento'  => 'required|date|before:-18 years',
            'tipo_documento'    => 'required|string|max:50',
            'numero_documento'  => [
                'required', 'string', 'max:50',
                Rule::unique('users', 'nro_document'),
            ],
            'pais'             => 'nullable|string|max:100',
            'tipo_cuenta'      => 'required|string|max:50',
            'metodo_pago'      => 'required|string|max:50',
            'referidor'        => 'required|string|max:255',
            'lado'             => 'required|in:izquierda,derecha',
            'preregistro_id'   => 'nullable|integer|exists:preregistros,id',
        ], [
            // Usuario
            'usuario.required'                        => 'El nombre de usuario es obligatorio.',
            'usuario.min'                             => 'El usuario debe tener al menos 4 caracteres.',
            'usuario.max'                             => 'El usuario no puede superar los 50 caracteres.',
            'usuario.regex'                           => 'El usuario solo puede contener letras, números y guion bajo (_). No se permiten espacios ni caracteres especiales.',
            'usuario.unique'                          => 'Este nombre de usuario ya está en uso. Por favor elige otro.',

            // Correo
            'correo.required'                         => 'El correo electrónico es obligatorio.',
            'correo.email'                            => 'El correo ingresado no es válido. Asegúrate de usar el formato: ejemplo@dominio.com',
            'correo.max'                              => 'El correo no puede superar los 255 caracteres.',
            'correo.unique'                           => 'Este correo ya está registrado en el sistema. Si ya tienes una cuenta, inicia sesión o usa la opción "Olvidé mi contraseña".',

            // Contraseña
            'password.required'                       => 'La contraseña es obligatoria.',
            'password.min'                            => 'La contraseña debe tener al menos 8 caracteres, incluir mayúscula, minúscula y número.',
            'password_confirm.required'               => 'Debes confirmar tu contraseña.',
            'password_confirm.same'                   => 'Las contraseñas no coinciden. Verifica que ambas sean iguales.',

            // Tipo usuario
            'tipo_usuario.required'                   => 'Selecciona el tipo de usuario.',

            // Nombre
            'nombre.required'                         => 'El nombre es obligatorio.',
            'nombre.max'                              => 'El nombre no puede superar los 255 caracteres.',
            'nombre.regex'                            => 'El nombre solo puede contener letras y espacios. No se permiten números ni símbolos.',

            // Apellido
            'apellido.required'                       => 'El apellido es obligatorio.',
            'apellido.max'                            => 'El apellido no puede superar los 255 caracteres.',
            'apellido.regex'                          => 'El apellido solo puede contener letras y espacios. No se permiten números ni símbolos.',

            // Teléfono
            'telefono.required'                       => 'El número de teléfono es obligatorio.',
            'telefono.regex'                          => 'El teléfono debe contener solo dígitos (entre 7 y 15 números), sin espacios ni guiones.',
            'telefono.unique'                         => 'Este número de teléfono ya está registrado en otra cuenta.',

            // Fecha de nacimiento
            'fecha_nacimiento.required'               => 'La fecha de nacimiento es obligatoria.',
            'fecha_nacimiento.date'                   => 'La fecha de nacimiento no tiene un formato válido.',
            'fecha_nacimiento.before'                 => 'Debes tener al menos 18 años para registrarte. Verifica tu fecha de nacimiento.',

            // Tipo documento
            'tipo_documento.required'                 => 'Selecciona el tipo de documento de identidad.',

            // Número documento
            'numero_documento.required'               => 'El número de documento es obligatorio.',
            'numero_documento.max'                    => 'El número de documento no puede superar los 50 caracteres.',
            'numero_documento.unique'                 => 'Este número de documento ya está registrado en otra cuenta.',

            // Lado
            'lado.required'                           => 'No se detectó el lado de registro. Vuelve al enlace original de invitación.',
            'lado.in'                                 => 'El lado de registro no es válido.',

            // Referidor
            'referidor.required'                      => 'No se encontró el usuario que te invitó.',

            // Preregistro
            'preregistro_id.exists'                   => 'El preregistro indicado no existe o ya fue procesado.',
        ]);

        // ─── Validación adicional del número de documento según tipo ─────────
        $this->validateDocumentNumber($request);

        try {
            $sponsor = User::where('username', $data['referidor'])->first();

            if (! $sponsor) {
                throw ValidationException::withMessages([
                    'referidor' => 'No encontramos al usuario que te invitó ("' . $data['referidor'] . '"). Verifica que el enlace de invitación sea correcto.',
                ]);
            }

            $accountType = $this->resolvePreregistroAccountType();
            $country     = $this->resolveCountry($data['pais'] ?? null);
            $documentType = $this->resolveDocumentType($data['tipo_documento']);
            $amount      = number_format(
                $accountType->price + ($accountType->price * ($accountType->iva / 100)),
                2, '.', ''
            );

            $openpayId     = config('services.openpay.id');
            $openpaySecret = config('services.openpay.sk');

            if (empty($openpayId) || empty($openpaySecret)) {
                Log::error('Credenciales de Openpay no configuradas para preregistro');

                return response()->json([
                    'message' => 'El sistema de pagos no está configurado correctamente. Por favor contacta al soporte técnico.',
                ], 500);
            }

            $openpay = \Openpay\Data\Openpay::getInstance($openpayId, $openpaySecret, 'PE', $request->ip());
            \Openpay\Data\Openpay::setProductionMode(false);

            $orderId = substr('preregistro-' . Str::uuid(), 0, 100);
            $encodedPassword = Hash::make($data['password']);
            $binaryPosition   = $data['lado'] === 'izquierda' ? 0 : 1;

            $charge = $openpay->charges->create([
                'order_id'    => $orderId,
                'method'      => 'card',
                'currency'    => 'USD',
                'amount'      => $amount,
                'description' => 'Pago preregistro Promolider',
                'customer'    => [
                    'name'         => $data['nombre'],
                    'last_name'    => $data['apellido'],
                    'phone_number' => $data['telefono'],
                    'email'        => $data['correo'],
                ],
                'send_email'   => false,
                'confirm'      => false,
                'redirect_url' => url('/login'),
            ]);

            $deletedPendingCount = $this->deletePendingPreregistroUsers($data['correo'], $data['preregistro_id'] ?? null);

            $unverifiedUser = new UnverifiedUser();
            $unverifiedUser->username        = $data['usuario'];
            $unverifiedUser->password        = $encodedPassword;
            $unverifiedUser->openpay_order_id = $charge->id;
            $unverifiedUser->data = json_encode([
                'id_referrer_sponsor' => $sponsor->id,
                'username'            => $data['usuario'],
                'password'            => $encodedPassword,
                'email'               => $data['correo'],
                'user_type'           => $this->resolveRoleName($data['tipo_usuario']),
                'name'                => $data['nombre'],
                'last_name'           => $data['apellido'],
                'biography'           => 'Registro desde preregistro',
                'phone'               => $data['telefono'],
                'date_birth'          => $data['fecha_nacimiento'],
                'id_document_type'    => $documentType->id,
                'nro_document'        => $data['numero_documento'],
                'id_country'          => $country->id,
                'id_account_type'     => $accountType->id,
                'purchase_number'     => $orderId,
                'payment_method_id'   => 1,
                'payment_method'      => 'openpay',
                'operation_number'    => $charge->id,
                'openpay'             => true,
                'binary_position'     => $binaryPosition,
                'preregistro_id'      => $data['preregistro_id'] ?? null,
            ]);
            $unverifiedUser->save();

            Log::info('Cargo Openpay de preregistro creado', [
                'order_id'        => $orderId,
                'charge_id'       => $charge->id,
                'correo'          => $data['correo'],
                'sponsor_id'      => $sponsor->id,
                'binary_position' => $binaryPosition,
                'deleted_pending' => $deletedPendingCount,
            ]);

            return response()->json([
                'payment_url' => $charge->payment_method->url,
                'charge_id'   => $charge->id,
            ]);

        } catch (ValidationException $e) {
            throw $e;

        } catch (\Openpay\Data\OpenpayApiTransactionError $e) {
            Log::error('Error de transacción Openpay (preregistro)', [
                'message'    => $e->getMessage(),
                'error_code' => $e->getErrorCode(),
                'correo'     => $data['correo'] ?? null,
            ]);

            $userMessage = $this->humanizeOpenpayError($e->getErrorCode(), $e->getMessage());

            return response()->json([
                'message' => $userMessage,
            ], 422);

        } catch (\Openpay\Data\OpenpayApiConnectionError $e) {
            Log::error('Error de conexión Openpay (preregistro)', ['message' => $e->getMessage()]);

            return response()->json([
                'message' => 'No pudimos conectar con la pasarela de pago en este momento. Espera unos minutos e intenta nuevamente.',
            ], 503);

        } catch (\Openpay\Data\OpenpayApiAuthError $e) {
            Log::error('Error de autenticación Openpay (preregistro)', ['message' => $e->getMessage()]);

            return response()->json([
                'message' => 'El sistema de pagos no está configurado correctamente. Contacta al soporte.',
            ], 500);

        } catch (\Throwable $exception) {
            Log::error('Error creando cargo Openpay de preregistro', [
                'message' => $exception->getMessage(),
                'trace'   => $exception->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Ocurrió un error inesperado al procesar tu pago. Por favor intenta de nuevo o contacta al soporte.',
                'details' => app()->environment('local') ? $exception->getMessage() : null,
            ], 500);
        }
    }

    // ─── Validación adicional del número de documento ────────────────────────
    private function validateDocumentNumber(Request $request): void
    {
        $tipo   = $request->input('tipo_documento');
        $numero = $request->input('numero_documento', '');

        $error = null;

        switch (strtolower(trim($tipo))) {
            case 'dni':
                if (! preg_match('/^[0-9]{8}$/', $numero)) {
                    $error = 'El DNI debe tener exactamente 8 dígitos numéricos. No se permiten letras ni espacios.';
                }
                break;

            case 'carnet_extranjeria':
                if (! preg_match('/^[A-Za-z0-9]{6,12}$/', $numero)) {
                    $error = 'El Carnet de Extranjería debe tener entre 6 y 12 caracteres alfanuméricos.';
                }
                break;

            case 'pasaporte':
                if (! preg_match('/^[A-Za-z0-9]{6,20}$/', $numero)) {
                    $error = 'El pasaporte debe tener entre 6 y 20 caracteres alfanuméricos.';
                }
                break;
        }

        if ($error) {
            throw ValidationException::withMessages([
                'numero_documento' => $error,
            ]);
        }
    }

    // ─── Traducción amigable de errores Openpay ───────────────────────────────
    private function humanizeOpenpayError(?string $errorCode, string $fallback): string
    {
        $messages = [
            '1000' => 'El servicio de pago no está disponible por mantenimiento. Intenta en unos minutos.',
            '1001' => 'Los datos del formulario de pago son incorrectos. Verifica la información e intenta de nuevo.',
            '1002' => 'No tienes permiso para realizar esta operación.',
            '1003' => 'La operación no pudo completarse porque los parámetros enviados son incorrectos.',
            '1004' => 'Hubo un error interno del servicio de pago. Intenta en unos minutos.',
            '1005' => 'El recurso solicitado no existe.',
            '1006' => 'Ya existe una transacción con este número de orden.',
            '1007' => 'La transferencia de fondos no fue aceptada por la institución financiera.',
            '1008' => 'Esta cuenta está desactivada. Contacta al soporte.',
            '1009' => 'El cuerpo de la solicitud es demasiado grande.',
            '1010' => 'La versión de la API utilizada ya no está soportada.',
            '2001' => 'La cuenta bancaria ya se encuentra registrada.',
            '2002' => 'La tarjeta bancaria ya se encuentra registrada.',
            '2003' => 'Este cliente ya existe con el mismo identificador externo.',
            '2004' => 'El número de tarjeta ingresado no es válido. Verifica el número y vuelve a intentarlo.',
            '2005' => 'La fecha de vencimiento de la tarjeta es anterior a la fecha actual. Usa una tarjeta vigente.',
            '2006' => 'El código de seguridad (CVV/CVC) de la tarjeta no fue proporcionado o es incorrecto.',
            '2007' => 'El número de tarjeta ingresado es de prueba y no es válido en producción.',
            '2008' => 'La tarjeta no es válida para este pago.',
            '2009' => 'El código de seguridad (CVV/CVC) ingresado es incorrecto. Verifícalo e intenta de nuevo.',
            '2010' => 'La autenticación 3D Secure falló. El banco rechazó la verificación de identidad.',
            '2011' => 'El tipo de tarjeta no está soportado. Intenta con Visa o Mastercard.',
            '3001' => 'La tarjeta fue rechazada por el banco. Verifica que tenga fondos suficientes o contacta a tu banco.',
            '3002' => 'La tarjeta ha expirado. Por favor usa una tarjeta vigente.',
            '3003' => 'La tarjeta no tiene fondos suficientes para completar el pago.',
            '3004' => 'La tarjeta fue reportada como robada. Contacta a tu banco.',
            '3005' => 'La tarjeta fue rechazada por el sistema antifraude. Contacta a tu banco.',
            '3006' => 'La operación no está permitida para esta tarjeta o cliente.',
            '3008' => 'La tarjeta no está habilitada para compras por internet. Actívala en tu banco.',
            '3009' => 'La tarjeta fue reportada como perdida. Contacta a tu banco.',
            '3010' => 'El banco ha restringido la tarjeta para pagos en línea.',
            '3011' => 'El banco solicitó que se retenga la tarjeta. Contacta a tu banco.',
            '3012' => 'Se requiere solicitar autorización al banco antes de realizar este pago.',
            '4001' => 'La cuenta de Openpay no tiene fondos suficientes para procesar la devolución.',
        ];

        return $messages[$errorCode] ?? 'El pago fue rechazado: ' . $fallback . '. Intenta con otra tarjeta o contacta al soporte.';
    }

    // ─── Helpers privados ─────────────────────────────────────────────────────

    private function resolvePreregistroAccountType(): AccountType
    {
        $configuredId = config('services.preregistro.account_type_id');

        if ($configuredId) {
            $accountType = AccountType::where('id', $configuredId)->where('status', '1')->first();
            if ($accountType) return $accountType;
        }

        return AccountType::where('status', '1')->where('price', 53.10)->first()
            ?? AccountType::where('status', '1')->where('account', 'Guest')->first()
            ?? AccountType::where('status', '1')->where('price', '>', 0)->orderBy('price')->firstOrFail();
    }

    private function resolveCountry(?string $countryName): Country
    {
        if ($countryName) {
            $country = Country::where('name', $countryName)->first()
                ?? Country::where('name', 'like', '%' . $countryName . '%')->first();

            if ($country) return $country;
        }

        return Country::where('name', 'Perú')->first()
            ?? Country::where('name', 'Peru')->first()
            ?? Country::firstOrFail();
    }

    private function resolveDocumentType(string $documentType): DocumentType
    {
        $normalized = strtolower(trim($documentType));

        return DocumentType::whereRaw('LOWER(document) = ?', [$normalized])->first()
            ?? DocumentType::where('document', 'like', '%' . $documentType . '%')->first()
            ?? DocumentType::firstOrFail();
    }

    private function resolveRoleName(string $role): string
    {
        return strtolower($role) === 'distribuidor' ? 'Distributor' : $role;
    }

    private function findPendingPreregistroUser(?string $correo, $preregistroId = null): ?UnverifiedUser
    {
        if (! $correo) {
            return null;
        }

        return $this->pendingPreregistroUsersQuery($correo, $preregistroId)
            ->latest()
            ->first();
    }

    private function deletePendingPreregistroUsers(string $correo, $preregistroId = null): int
    {
        return $this->pendingPreregistroUsersQuery($correo, $preregistroId)->delete();
    }

    private function pendingPreregistroUsersQuery(string $correo, $preregistroId = null)
    {
        return UnverifiedUser::where(function ($query) use ($correo, $preregistroId) {
            $query->where('data->email', $correo);

            if ($preregistroId) {
                $query->orWhere('data->preregistro_id', (int) $preregistroId);
            }
        });
    }



    private function buildPendingRegistrationPrefill(
        UnverifiedUser $unverified,
        Preregistro $preregistro,
        string $username,
        ?string $lado
    ): array {
        $data = json_decode($unverified->data, true) ?: [];

        return [
            'usuario' => $data['username'] ?? $unverified->username,
            'correo' => $data['email'] ?? $preregistro->correo,
            'tipo_usuario' => $this->normalizeRoleForPreregistro($data['user_type'] ?? 'distribuidor'),
            'nombre' => $data['name'] ?? $preregistro->nombres,
            'apellido' => $data['last_name'] ?? $preregistro->apellidos,
            'telefono' => $data['phone'] ?? $preregistro->whatsapp,
            'fecha_nacimiento' => $data['date_birth'] ?? '',
            'tipo_documento' => $this->normalizeDocumentTypeForPreregistro($data['id_document_type'] ?? null),
            'numero_documento' => $data['nro_document'] ?? '',
            'pais' => optional(Country::find($data['id_country'] ?? null))->name ?? 'Peru',
            'tipo_cuenta' => 'pre_registro',
            'metodo_pago' => 'tarjeta',
            'referidor' => $username,
            'lado' => $lado ?: (($data['binary_position'] ?? null) === 0 ? 'izquierda' : 'derecha'),
            'preregistro_id' => $preregistro->id,
            'reuse_pending_registration' => true,
        ];
    }

    private function normalizeRoleForPreregistro(string $role): string
    {
        return strtolower($role) === 'distributor' ? 'distribuidor' : strtolower($role);
    }

    private function normalizeDocumentTypeForPreregistro($documentTypeId): string
    {
        $document = optional(DocumentType::find($documentTypeId))->document;
        $normalized = strtolower(trim((string) $document));

        if (str_contains($normalized, 'dni')) return 'dni';
        if (str_contains($normalized, 'extranjer')) return 'carnet_extranjeria';
        if (str_contains($normalized, 'pasaporte')) return 'pasaporte';

        return $normalized ?: '';
    }

    public function generateLink(Request $request)
    {
        $request->validate([
            'username' => 'required|string|exists:users,username',
        ]);

        $user = User::where('username', $request->username)->first();

        $config = PreregistroLink::firstOrCreate(
            [
                'username' => $user->username,
            ],
            [
                'lado' => 'izquierda',
                'landing' => 'claro',
            ]
        );

        return response()->json([
            'url' => url('/preregistro/' . $user->username),
            'config' => [
                'lado' => $config->lado,
                'landing' => $config->landing,
            ]
        ]);
    }

    public function saveConfig(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|exists:users,username',
            'lado' => 'required|in:izquierda,derecha',
            'landing' => 'required|in:claro,oscuro',
        ]);

        $link = PreregistroLink::updateOrCreate(
            [
                'username' => $data['username'],
            ],
            [
                'lado' => $data['lado'],
                'landing' => $data['landing'],
            ]
        );

        return response()->json([
            'ok' => true,
            'config' => $link,
        ]);
    }
    public function getConfig($username)
    {
        $config = PreregistroLink::firstOrCreate(
            [
                'username' => $username,
            ],
            [
                'lado' => 'izquierda',
                'landing' => 'claro',
            ]
        );

        return response()->json($config);
    }

    /**
     * Verificar si un campo (usuario, correo, numero_documento) ya está
     * registrado en users o unverified_users.
     */
    public function checkDuplicate(Request $request)
    {
        $field = $request->query('field');
        $value = $request->query('value');

        if (! $field || ! $value) {
            return response()->json(['taken' => false]);
        }

        $taken   = false;
        $message = '';

        switch ($field) {
            case 'usuario':
                $inUsers = User::where('username', $value)->exists();
                $inUnverified = UnverifiedUser::where('username', $value)->exists();

                if ($inUsers || $inUnverified) {
                    $taken   = true;
                    $message = 'Este nombre de usuario ya está en uso. Por favor elige otro.';
                }
                break;

            case 'correo':
                $inUsers = User::where('email', $value)->exists();

                if ($inUsers) {
                    $taken   = true;
                    $message = 'Este correo ya está registrado en el sistema. Si ya tienes una cuenta, inicia sesión.';
                }
                break;

            case 'numero_documento':
                $inUsers = User::where('nro_document', $value)->exists();

                if ($inUsers) {
                    $taken   = true;
                    $message = 'Este número de documento ya está registrado en otra cuenta.';
                }
                break;
        }

        return response()->json([
            'taken'   => $taken,
            'message' => $message,
        ]);
    }
}
