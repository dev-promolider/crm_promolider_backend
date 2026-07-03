<?php
namespace Promolider\Domain\Registration\Ports\Out;

interface NotificationServiceInterface
{
    /**
     * Envía correo de bienvenida al usuario registrado vía Amazon SES.
     */
    public function sendWelcomeEmail(string $email, string $username, string $password): void;

    /**
     * Envía los datos del preregistro al webhook de n8n para automatización.
     */
    public function sendPreregistroWebhook(array $payload): void;

    /**
     * Envía un evento de tracking (radar) al webhook de n8n.
     */
    public function sendRadarEvent(array $payload): void;
}
