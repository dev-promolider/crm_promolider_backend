<?php
namespace Promolider\Application\Registration\UseCases;

use App\Models\UnverifiedUser;
use Promolider\Domain\Registration\Entities\RegistrationUser;
use Promolider\Domain\Registration\Ports\Out\NotificationServiceInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class ProcessOpenpayWebhookUseCase
{
    public function __construct(
        private CreateRegisteredUserUseCase $createRegisteredUserUseCase,
        private NotificationServiceInterface $notificationService
    ) {}

    public function execute(array $payload): void
    {
        Log::info('[WEBHOOK OPENPAY] Webhook recibido', ['payload' => $payload]);

        if (($payload['type'] ?? '') === 'verification') {
            Log::info('[WEBHOOK OPENPAY] Tipo de webhook: verification', ['request' => $payload]);
            return;
        }

        if (!isset($payload['transaction']) || !is_array($payload['transaction'])) {
            Log::info('[WEBHOOK OPENPAY] Webhook sin transaction, ignorando', ['payload' => $payload]);
            return;
        }

        $transaction = $payload['transaction'];
        $transactionId = $transaction['id'] ?? null;
        $status = $transaction['status'] ?? null;

        Log::info('[WEBHOOK OPENPAY] Tipo de webhook: transaction', [
            'transaction_id' => $transactionId,
            'status'         => $status
        ]);

        if ($status !== 'completed' || !$transactionId) {
            return;
        }

        // ==========================================
        // NUEVO: Verificar si es un pago OPC (recompra)
        // ==========================================
        if (Cache::has('opc_intent_' . $transactionId)) {
            Log::info('[WEBHOOK OPENPAY] Detectado pago OPC (recompra)', ['order_id' => $transactionId]);
            try {
                app(\Promolider\Application\Wallet\UseCases\OPC\ConfirmOpcOpenpayPaymentUseCase::class)->execute($transactionId);
                Log::info('[WEBHOOK OPENPAY] Pago OPC procesado exitosamente', ['order_id' => $transactionId]);
            } catch (Exception $e) {
                Log::error('[WEBHOOK OPENPAY] Error al procesar pago OPC', ['error' => $e->getMessage()]);
            }
            return;
        }
        
        // ==========================================
        // Flujo existente: Preregistro
        // ==========================================

        // Buscar el usuario no verificado por el openpay_order_id
        $unverifiedUser = UnverifiedUser::where('openpay_order_id', $transactionId)->first();

        if (!$unverifiedUser) {
            Log::warning('[WEBHOOK OPENPAY] No se encontró UnverifiedUser para transaction_id', ['order_id' => $transactionId]);
            return;
        }

        Log::info('[WEBHOOK OPENPAY] Se encontró UnverifiedUser', ['order_id' => $transactionId]);

        $data = is_string($unverifiedUser->data) ? json_decode($unverifiedUser->data, true) : $unverifiedUser->data;
        if (!$data) {
            Log::error('[WEBHOOK OPENPAY] Datos del UnverifiedUser inválidos', ['data' => $unverifiedUser->data]);
            return;
        }

        // Mappear los datos a la entidad de dominio RegistrationUser
        $registrationUser = new RegistrationUser(
            username: $data['username'] ?? '',
            email: $data['email'] ?? '',
            name: $data['name'] ?? '',
            lastName: $data['last_name'] ?? '',
            phone: $data['phone'] ?? '',
            dateBirth: $data['date_birth'] ?? null,
            idCountry: (int) ($data['id_country'] ?? 0),
            idDocumentType: (int) ($data['id_document_type'] ?? 0),
            nroDocument: $data['nro_document'] ?? '',
            idAccountType: (int) ($data['id_account_type'] ?? 0),
            idReferrerSponsor: (int) ($data['id_referrer_sponsor'] ?? 0),
            password: $data['password'] ?? '', // hash en DB
            biography: $data['biography'] ?? '',
            position: isset($data['binary_position']) ? (int) $data['binary_position'] : null,
            photo: null,
            userType: $data['user_type'] ?? null
        );

        $paymentData = [
            'id_payment_method' => $data['payment_method_id'] ?? 1,
            'operation_number'  => $transactionId,
            'amount'            => 150, // default para membresía
            'id_user_sponsor'   => (int) ($data['id_referrer_sponsor'] ?? 0),
        ];

        try {
            Log::info('[WEBHOOK OPENPAY] Llamando a CreateRegisteredUserUseCase para procesar el registro final');
            
            // RawPassword no está en los datos de UnverifiedUser (se guardaba hasheado). 
            // Para el correo de bienvenida no se enviaba el password plano original si se perdía de sesión.
            // Pero como la DB guarda el hash, podemos pasar null.
            $this->createRegisteredUserUseCase->execute($registrationUser, $paymentData, '');

            Log::info('[WEBHOOK OPENPAY] Usuario creado con éxito en tablas principales', ['order_id' => $transactionId]);

            // Eliminar registro temporal
            $unverifiedUser->delete();
            Log::info('[WEBHOOK OPENPAY] UnverifiedUser eliminado exitosamente');

            // Notificar a N8N
            $fullName = trim(($data['name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
            $this->notificationService->sendPreregistroWebhook([
                'nombres' => $fullName,
                'correo'  => $data['email'] ?? '',
                'estado'  => 'pagado'
            ]);

        } catch (Exception $e) {
            Log::error('[WEBHOOK OPENPAY] Error al crear usuario desde webhook', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);
        }
    }
}
