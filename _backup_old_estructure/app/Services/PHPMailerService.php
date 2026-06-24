<?php
// app/Services/PHPMailerService.php
//  Servicio de envío de emails usando Mailrelay API
//  Configurado específicamente para Mailrelay (IPZMarketing)
// Mantiene la misma interfaz pública - NO requiere cambios en el código existente

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PHPMailerService
{
    private $apiKey;
    private $apiEndpoint;
    private $fromEmail;
    private $fromName;

    public function __construct()
    {
        $this->configureMailrelayAPI();
    }

    /**
     * Configura la API de Mailrelay
     */
    private function configureMailrelayAPI()
    {
        try {
            // Obtener configuración del .env
            $this->apiKey = env('EMAIL_API_KEY');
            $this->fromEmail = env('EMAIL_API_FROM_EMAIL', 'soporte@promolider.info');
            $this->fromName = env('EMAIL_API_FROM_NAME', 'Promolider');

            if (!$this->apiKey) {
                throw new \Exception('EMAIL_API_KEY no configurada en .env');
            }

            // Configurar endpoint de Mailrelay
            $subdomain = env('MAILRELAY_SUBDOMAIN', 'promolider3');
            $this->apiEndpoint = "https://{$subdomain}.ipzmarketing.com/api/v1/send_emails";

            Log::info('Mailrelay API configurada exitosamente', [
                'endpoint' => $this->apiEndpoint,
                'from_email' => $this->fromEmail,
                'from_name' => $this->fromName
            ]);

        } catch (\Exception $e) {
            Log::error('Error configurando Mailrelay API: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Prepara los datos del email en el formato de Mailrelay
     */
    private function prepareEmailData($to, $subject, $body, $fromName = null)
    {
        $fromName = $fromName ?? $this->fromName;

        // Formato Mailrelay API - from/to como objetos + html_part/text_part
        return [
            'from' => [
                'email' => $this->fromEmail,
                'name' => $fromName
            ],
            'to' => [
                [
                    'email' => $to
                ]
            ],
            'subject' => $subject,
            'html_part' => $body,
            'text_part' => strip_tags($body)
        ];
    }

    /**
     * Obtiene los headers HTTP para Mailrelay
     */
    private function getHeaders()
    {
        return [
            'X-AUTH-TOKEN' => $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];
    }

    /**
     * Envía un email usando Mailrelay API
     * 
     * @param string $to Email destinatario
     * @param string $subject Asunto del correo
     * @param string $body Contenido HTML del correo
     * @param string $altBody Texto alternativo (mantenido por compatibilidad, no se usa)
     * @param string|null $fromName Nombre del remitente (opcional)
     * @return bool True si se envió correctamente
     */
    public function sendEmail($to, $subject, $body, $altBody = '', $fromName = null)
    {
        try {
            $emailData = $this->prepareEmailData($to, $subject, $body, $fromName);
            $headers = $this->getHeaders();

            // Enviar petición HTTP a Mailrelay
            // withOptions(['verify' => false]) desactiva verificación SSL (necesario en XAMPP)
            $response = Http::withHeaders($headers)
                ->withOptions(['verify' => false])
                ->timeout(30)
                ->post($this->apiEndpoint, $emailData);

            if ($response->successful()) {
                $responseData = $response->json();
                
                Log::info('Email enviado exitosamente con Mailrelay', [
                    'to' => $to,
                    'subject' => $subject,
                    'from' => $this->fromEmail,
                    'response' => $responseData
                ]);

                return true;
            } else {
                throw new \Exception(
                    "Error en Mailrelay API: HTTP {$response->status()} - {$response->body()}"
                );
            }

        } catch (\Exception $e) {
            Log::error('Error enviando email con Mailrelay: ' . $e->getMessage(), [
                'to' => $to,
                'subject' => $subject,
                'error_class' => get_class($e)
            ]);
            throw $e;
        }
    }

    /**
     * Envía un email usando una plantilla Blade de Laravel
     * 
     * @param string $to Email destinatario
     * @param string $subject Asunto del correo
     * @param string $template Nombre de la vista Blade (ej: 'emails.welcome')
     * @param array $data Datos para pasar a la plantilla
     * @param string|null $fromName Nombre del remitente (opcional)
     * @return bool True si se envió correctamente
     */
    public function sendEmailWithTemplate($to, $subject, $template, $data = [], $fromName = null)
    {
        try {
            $htmlBody = view($template, $data)->render();
            return $this->sendEmail($to, $subject, $htmlBody, '', $fromName);
        } catch (\Exception $e) {
            Log::error('Error renderizando plantilla: ' . $e->getMessage(), [
                'template' => $template,
                'to' => $to
            ]);
            throw $e;
        }
    }

    /**
     * Envía el mismo email a múltiples destinatarios
     * 
     * @param array $recipients Array de emails destinatarios
     * @param string $subject Asunto del correo
     * @param string $body Contenido HTML del correo
     * @param string $altBody Texto alternativo (mantenido por compatibilidad)
     * @param string|null $fromName Nombre del remitente (opcional)
     * @return array Resultados del envío para cada destinatario
     */
    public function sendToMultiple($recipients, $subject, $body, $altBody = '', $fromName = null)
    {
        $results = [];
        foreach ($recipients as $recipient) {
            try {
                $result = $this->sendEmail($recipient, $subject, $body, $altBody, $fromName);
                $results[$recipient] = ['success' => true, 'result' => $result];
            } catch (\Exception $e) {
                $results[$recipient] = ['success' => false, 'error' => $e->getMessage()];
            }
        }
        return $results;
    }

    /**
     * Envía un email de prueba
     * 
     * @param string $email Email destinatario
     * @return bool True si se envió correctamente
     */
    public function sendTestEmail($email)
    {
        $subject = 'Correo de Prueba - Mailrelay Promolider';
        $body = view('emails.test-template', [
            'email' => $email,
            'timestamp' => now()->format('d/m/Y H:i:s')
        ])->render();
        
        return $this->sendEmail($email, $subject, $body);
    }
}
