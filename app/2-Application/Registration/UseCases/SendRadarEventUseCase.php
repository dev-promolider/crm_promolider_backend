<?php
namespace Promolider\Application\Registration\UseCases;

use Promolider\Domain\Registration\Ports\Out\NotificationServiceInterface;
use Promolider\Domain\Registration\Ports\Out\PreregistroRepositoryInterface;

class SendRadarEventUseCase
{
    public function __construct(
        private NotificationServiceInterface $notificationService,
        private PreregistroRepositoryInterface $preregistroRepository
    ) {}

    /**
     * Recibe un evento del frontend (tracking del funnel) y lo reenvía a n8n.
     * Enriquece el payload con datos del referidor desde la DB.
     * 
     * Lógica extraída de: PreregistroController::radar()
     */
    public function execute(array $eventData): void
    {
        $preregistroId = $eventData['preregistro_id'] ?? null;

        // Enriquecer con datos del referidor si existe el preregistro
        $referrerData = [
            'referrer_nombre'   => '',
            'referrer_correo'   => '',
            'referrer_whatsapp' => '',
            'username'          => '',
        ];

        if ($preregistroId) {
            $preregistro = $this->preregistroRepository->findById($preregistroId);

            if ($preregistro) {
                $referrerData = [
                    'referrer_nombre'   => $preregistro->referrerNombre ?? '',
                    'referrer_correo'   => $preregistro->referrerCorreo ?? '',
                    'referrer_whatsapp' => $preregistro->referrerWhatsapp ?? '',
                    'username'          => $preregistro->referrerUsername ?? '',
                ];
            }
        }

        $payload = array_merge([
            'evento'         => $eventData['evento'] ?? 'desconocido',
            'nombres'        => $eventData['nombres'] ?? '',
            'apellidos'      => $eventData['apellidos'] ?? '',
            'correo'         => $eventData['correo'] ?? '',
            'whatsapp'       => $eventData['whatsapp'] ?? '',
            'preregistro_id' => $preregistroId,
            'paso_actual'    => $eventData['paso_actual'] ?? '',
            'timestamp'      => $eventData['timestamp'] ?? now()->toIso8601String(),
        ], $referrerData);

        $this->notificationService->sendRadarEvent($payload);
    }
}
