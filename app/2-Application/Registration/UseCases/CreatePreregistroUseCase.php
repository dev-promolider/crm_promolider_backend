<?php
namespace Promolider\Application\Registration\UseCases;

use Promolider\Domain\Registration\Ports\Out\PreregistroRepositoryInterface;
use Promolider\Domain\Registration\Ports\Out\NotificationServiceInterface;
use Exception;

class CreatePreregistroUseCase
{
    public function __construct(
        private PreregistroRepositoryInterface $preregistroRepository,
        private NotificationServiceInterface $notificationService,
        private \Promolider\Domain\Registration\Ports\Out\RegistrationRepositoryInterface $registrationRepository,
        private \App\Services\MLM\BinaryTreeService $binaryTreeService
    ) {}

    /**
     * Crea un nuevo preregistro y envía la señal al webhook de n8n.
     * 
     * Lógica extraída de: PreregistroController::store()
     */
    public function execute(string $username, array $data): array
    {
        $email = $data['correo'];

        // 1. ¿Ya existe como usuario real?
        if ($this->preregistroRepository->emailExistsAsUser($email)) {
            return [
                'status' => 'user_exists',
                'redirect_url' => '/login',
            ];
        }

        // 2. ¿Existe preregistro previo?
        $existing = $this->preregistroRepository->findByEmail($email);

        if ($existing) {
            // 3. ¿Tiene pago pendiente?
            if ($existing->isPendingPayment()) {
                return [
                    'status' => 'payment_pending',
                    'preregistro_id' => $existing->id,
                    'username' => $username,
                    'side' => $existing->side,
                ];
            }

            // Preregistro normal ya existe
            return [
                'status' => 'already_registered',
                'preregistro_id' => $existing->id,
                'username' => $username,
                'side' => $existing->side,
            ];
        }

        // 4. Crear nuevo preregistro
        $lado = $data['lado'] ?? null;
        if ($lado === 'automatico') {
            $sponsor = $this->registrationRepository->findSponsorByUsername($username);
            if ($sponsor) {
                $weakerLeg = $this->binaryTreeService->getWeakerLeg($sponsor['id']);
                $lado = $weakerLeg === 0 ? 'izquierda' : 'derecha';
            } else {
                $lado = 'izquierda';
            }
        }

        $preregistro = $this->preregistroRepository->create([
            'nombres'           => $data['nombres'],
            'apellidos'         => $data['apellidos'],
            'correo'            => $email,
            'whatsapp'          => $data['whatsapp'],
            'referrer_username' => $username,
            'lado'              => $lado,
            'referrer_nombre'   => $data['referrer_nombre'] ?? '',
            'referrer_apellido' => $data['referrer_apellido'] ?? '',
            'referrer_correo'   => $data['referrer_correo'] ?? '',
            'referrer_whatsapp' => $data['referrer_whatsapp'] ?? '',
            'url_invitacion'    => $data['url_invitacion'] ?? '',
        ]);

        // 5. Construir payload para n8n
        $webhookPayload = [
            'username'           => $username,
            'lado'               => $preregistro->side,
            'preregistro_id'     => $preregistro->id,
            'nombres'            => $data['nombres'],
            'apellidos'          => $data['apellidos'],
            'correo'             => $email,
            'whatsapp'           => $data['whatsapp'],
            'url_invitacion'     => $data['url_invitacion'] ?? '',
            'referrer_nombre'    => $data['referrer_nombre'] ?? '',
            'referrer_apellido'  => $data['referrer_apellido'] ?? '',
            'referrer_correo'    => $data['referrer_correo'] ?? '',
            'referrer_whatsapp'  => $data['referrer_whatsapp'] ?? '',
            'access_token'       => $preregistro->accessToken,
            'token_expires_at'   => $preregistro->tokenExpiresAt?->format(DATE_ATOM),
            'retorno_url'        => config('app.frontend_url') . '/registro?token=' . $preregistro->accessToken,
        ];

        // 6. Enviar señal a n8n (no bloquea el registro si falla)
        try {
            $this->notificationService->sendPreregistroWebhook($webhookPayload);
        } catch (Exception $e) {
            // Se loguea pero no se propaga — el preregistro ya fue creado
        }

        return [
            'status' => 'created',
            'preregistro_id' => $preregistro->id,
            'username' => $username,
            'side' => $preregistro->side,
            'redirect_url' => '/mi-dashboard?token=' . $preregistro->accessToken . '&pr_nombres=' . urlencode($data['nombres']),
        ];
    }
}
