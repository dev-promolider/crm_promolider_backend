<?php
namespace Promolider\Infrastructure\Registration\Out\Notifications;

use Promolider\Domain\Registration\Ports\Out\NotificationServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SesNotificationService implements NotificationServiceInterface
{
    /**
     * Envía correo de bienvenida al usuario registrado vía Amazon SES.
     */
    public function sendWelcomeEmail(string $email, string $username, string $password): void
    {
        // TODO: implement
    }

    /**
     * Envía los datos del preregistro al webhook de n8n para automatización.
     */
    public function sendPreregistroWebhook(array $payload): void
    {
        $webhookUrl = config('services.n8n.preregistro_webhook');

        if (empty($webhookUrl)) {
            Log::warning('[N8N] URL de webhook de preregistro no configurada');
            return;
        }

        Log::info('[N8N PREREGISTRO] Enviando señal', [
            'url'    => $webhookUrl,
            'correo' => $payload['correo'] ?? 'N/A',
        ]);

        try {
            $response = Http::timeout(15)
                ->withoutVerifying()
                ->acceptJson()
                ->post($webhookUrl, $payload);

            Log::info('[N8N PREREGISTRO] Respuesta recibida', [
                'http_status' => $response->status(),
                'correo'      => $payload['correo'] ?? 'N/A',
            ]);

            if ($response->failed()) {
                Log::error('[N8N PREREGISTRO] El webhook respondió con error', [
                    'http_status' => $response->status(),
                    'body'        => $response->body(),
                ]);
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('[N8N PREREGISTRO] Timeout o conexión rechazada', [
                'url'     => $webhookUrl,
                'message' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            Log::error('[N8N PREREGISTRO] Error inesperado', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Envía un evento de tracking (radar) al webhook de n8n.
     */
    public function sendRadarEvent(array $payload): void
    {
        $radarUrl = config('services.n8n.radar_webhook', 'https://ia.promolider.org/webhook-test/pre_pago');

        Log::info('[RADAR] Evento recibido', $payload);

        try {
            $response = Http::timeout(10)
                ->withoutVerifying()
                ->acceptJson()
                ->post($radarUrl, $payload);

            Log::info('[RADAR] Respuesta de n8n', [
                'http_status' => $response->status(),
            ]);
        } catch (\Throwable $e) {
            Log::error('[RADAR] Error enviando a n8n', [
                'message' => $e->getMessage(),
            ]);
        }
    }
}
