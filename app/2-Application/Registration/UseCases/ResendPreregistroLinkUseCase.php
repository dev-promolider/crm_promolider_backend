<?php
namespace Promolider\Application\Registration\UseCases;

use App\Models\Preregistro;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ResendPreregistroLinkUseCase
{
    public function execute(string $correo): array
    {
        $preregistro = Preregistro::where('correo', $correo)->first();

        if (! $preregistro) {
            return [
                'ok'      => true,
                'message' => 'Si tu correo tiene un preregistro, recibirás el enlace en breve.',
            ];
        }

        // No reenviar si ya es usuario registrado
        $userExists = User::where('email', $correo)->exists();
        if ($userExists) {
            return [
                'ok'      => false,
                'message' => 'Este correo ya tiene una cuenta activa. Inicia sesión directamente.',
            ];
        }

        // Regenerar token con 72 horas nuevas
        $preregistro->generateToken(72);

        $payload = [
            'evento'           => 'resend_link',
            'preregistro_id'   => $preregistro->id,
            'nombres'          => $preregistro->nombres,
            'apellidos'        => $preregistro->apellidos,
            'correo'           => $preregistro->correo,
            'whatsapp'         => $preregistro->whatsapp,
            'username'         => $preregistro->referrer_username,
            'lado'             => $preregistro->lado,
            'access_token'     => $preregistro->access_token,
            'token_expires_at' => optional($preregistro->token_expires_at)->format(DATE_ATOM),
            'retorno_url'      => config('app.frontend_url') . '/registro?token=' . $preregistro->access_token,
        ];

        $webhookUrl = config('services.n8n.preregistro_webhook');

        try {
            $response = Http::timeout(15)->acceptJson()->post($webhookUrl, $payload);

            Log::info('[PREREGISTRO RESEND] Token regenerado y señal enviada a n8n', [
                'preregistro_id' => $preregistro->id,
                'correo'         => $preregistro->correo,
                'http_status'    => $response->status(),
            ]);
        } catch (\Throwable $e) {
            Log::error('[PREREGISTRO RESEND] Error enviando a n8n', [
                'preregistro_id' => $preregistro->id,
                'message'        => $e->getMessage(),
            ]);
        }

        return [
            'ok'      => true,
            'message' => 'Si tu correo tiene un preregistro, recibirás el enlace en breve.',
        ];
    }
}
