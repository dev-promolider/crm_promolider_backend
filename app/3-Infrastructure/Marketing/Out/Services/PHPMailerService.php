<?php

namespace Promolider\Infrastructure\Marketing\Out\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PHPMailerService
{
    private $fromEmail;
    private $fromName;

    public function __construct()
    {
        $fromEmail = config('mail.from.address');
        if (!$fromEmail || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            $fromEmail = env('MAIL_FROM_ADDRESS');
        }
        if (!$fromEmail || !filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            $fromEmail = 'soporte@promolider.info';
        }
        $this->fromEmail = $fromEmail;
        $this->fromName = config('mail.from.name') ?: env('MAIL_FROM_NAME', 'Promolider');
    }

    public function sendEmail($to, $subject, $body, $altBody = '', $fromName = null)
    {
        try {
            $fromName = $fromName ?? $this->fromName;

            Mail::send([], [], function ($message) use ($to, $subject, $body, $altBody, $fromName) {
                $message->to($to)
                    ->subject($subject)
                    ->from($this->fromEmail, $fromName)
                    ->setBody($body, 'text/html');

                if ($altBody) {
                    $message->addPart($altBody, 'text/plain');
                }
            });

            Log::info('Email enviado exitosamente con SMTP', [
                'to' => $to,
                'subject' => $subject,
            ]);
            return true;

        } catch (\Exception $e) {
            Log::error('Error enviando email: ' . $e->getMessage(), [
                'to' => $to,
                'subject' => $subject,
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }

    public function sendEmailWithTemplate($to, $subject, $template, $data = [], $fromName = null)
    {
        try {
            $htmlBody = view($template, $data)->render();
            return $this->sendEmail($to, $subject, $htmlBody, '', $fromName);
        } catch (\Exception $e) {
            Log::error('Error renderizando plantilla: ' . $e->getMessage(), [
                'template' => $template,
                'to' => $to,
                'trace' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
}
