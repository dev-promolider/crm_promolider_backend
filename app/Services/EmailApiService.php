<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class EmailApiService
{
    private $apiKey;
    private $baseUrl;

    public function __construct()
    {
        $this->apiKey = env('EMAIL_API_KEY');
        $this->baseUrl = rtrim(env('EMAIL_API_BASE_URL'), '/');
        
        if (!$this->apiKey || !$this->baseUrl) {
            throw new Exception('EMAIL_API_KEY y EMAIL_API_BASE_URL deben estar configurados en .env');
        }
    }

    /**
     * Enviar email usando la API
     */
    public function sendEmail($to, $subject, $body, $isHtml = true)
    {
        try {
            Log::info('Enviando email via API', [
                'to' => $to,
                'subject' => $subject,
                'is_html' => $isHtml
            ]);

            $response = Http::withHeaders([
                'X-AUTH-TOKEN' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($this->baseUrl . '/api/v1/send_emails', [
                'from' => [
                    'email' => env('MAIL_FROM_ADDRESS'),
                    'name' => env('MAIL_FROM_NAME')
                ],
                'to' => [
                    [
                        'email' => $to,
                        'name' => ''
                    ]
                ],
                'subject' => $subject,
                'html_part' => $isHtml ? $body : null,
                'text_part' => !$isHtml ? $body : null,
                'text_part_auto' => $isHtml ? true : false
            ]);

            if ($response->successful()) {
                Log::info('Email enviado exitosamente via API', [
                    'to' => $to,
                    'response' => $response->json()
                ]);
                return true;
            } else {
                Log::error('Error al enviar email via API', [
                    'to' => $to,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return false;
            }

        } catch (Exception $e) {
            Log::error('Excepción al enviar email via API', [
                'to' => $to,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Enviar email con plantilla de Blade
     */
    public function sendEmailWithTemplate($to, $subject, $template, $data = [], $fromEmail = null, $fromName = null)
    {
        try {
            // Renderizar la plantilla de Blade
            $body = view($template, $data)->render();
            
            Log::info('Enviando email con plantilla via API', [
                'to' => $to,
                'subject' => $subject,
                'template' => $template
            ]);

            $response = Http::withHeaders([
                'X-AUTH-TOKEN' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($this->baseUrl . '/api/v1/send_emails', [
                'from' => [
                    'email' => $fromEmail ?: env('MAIL_FROM_ADDRESS'),
                    'name' => $fromName ?: env('MAIL_FROM_NAME')
                ],
                'to' => [
                    [
                        'email' => $to,
                        'name' => ''
                    ]
                ],
                'subject' => $subject,
                'html_part' => $body,
                'text_part_auto' => true
            ]);

            if ($response->successful()) {
                Log::info('Email con plantilla enviado exitosamente via API', [
                    'to' => $to,
                    'template' => $template,
                    'response' => $response->json()
                ]);
                return true;
            } else {
                Log::error('Error al enviar email con plantilla via API', [
                    'to' => $to,
                    'template' => $template,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return false;
            }

        } catch (Exception $e) {
            Log::error('Excepción al enviar email con plantilla via API', [
                'to' => $to,
                'template' => $template,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Probar conexión con la API
     */
    public function testConnection()
    {
        try {
            Log::info('Probando conexión con Email API...');

            $response = Http::withHeaders([
                'X-AUTH-TOKEN' => $this->apiKey,
                'Accept' => 'application/json'
            ])->get($this->baseUrl . '/api/v1/groups'); // Endpoint de prueba

            if ($response->successful()) {
                Log::info('Conexión exitosa con Email API');
                return true;
            } else {
                Log::error('Error de conexión con Email API', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                throw new Exception('Error al conectar con la API: ' . $response->status());
            }

        } catch (Exception $e) {
            Log::error('Excepción al probar conexión con Email API', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Enviar email de prueba
     */
    public function sendTestEmail($to)
    {
        $subject = 'Correo de Prueba - Email API';
        $body = '
            <h2>¡Conexión exitosa!</h2>
            <p>Este correo de prueba fue enviado usando la <strong>Email API</strong>.</p>
            <p>La integración está funcionando correctamente.</p>
            <p>Fecha: ' . now()->format('d/m/Y H:i:s') . '</p>
        ';

        return $this->sendEmail($to, $subject, $body, true);
    }
}