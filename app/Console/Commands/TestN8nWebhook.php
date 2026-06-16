<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestN8nWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notify:n8n-test
                            {--url= : URL del webhook de n8n (default: https://ia.promolider.org/webhook-test/pago_promolider)}
                            {--user= : Usuario para Basic Auth}
                            {--pass= : Contraseña para Basic Auth}
                            {--nombre=Test User : Nombre a enviar en el payload}
                            {--email=test@example.com : Email a enviar en el payload}
                            {--estado=pagado : Estado del pago}
                            {--insecure : Desactivar verificación SSL (solo para pruebas locales)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía una petición de prueba al webhook de n8n con Basic Auth para verificar la autenticación';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $url = $this->option('url') ?: 'https://ia.promolider.org/webhook-test/pago_promolider';

        // Las credenciales Basic Auth deben pasarse explícitamente por CLI
        // ya que este es un comando de prueba. No hay config persistente para ellas.
        $basicUser = $this->option('user');
        $basicPass = $this->option('pass');

        $payload = [
            'nombre'      => $this->option('nombre'),
            'email'       => $this->option('email'),
            'estado_pago' => $this->option('estado'),
        ];

        $this->info('=== TEST DE WEBHOOK N8N ===');
        $this->line("URL:     {$url}");
        $this->line("Method:  POST");
        $this->line("Payload: " . json_encode($payload, JSON_PRETTY_PRINT));

        if ($basicUser && $basicPass) {
            $this->line("Auth:    Basic Auth (user: {$basicUser})");
        } else {
            $this->warn("Auth:    Sin autenticación (no se configuró user/pass)");
        }

        $this->newLine();
        $this->info('Enviando petición...');

        try {
            $httpClient = Http::timeout(30)
                ->acceptJson()
                ->withHeaders(['Content-Type' => 'application/json']);

            // En pruebas locales con WAMP, el certificado SSL puede no estar configurado
            if ($this->option('insecure')) {
                $httpClient->withoutVerifying();
                $this->warn('SSL: Verificación desactivada (--insecure)');
            }

            // Agregar Basic Auth si se proporcionaron credenciales
            if ($basicUser && $basicPass) {
                $httpClient->withBasicAuth($basicUser, $basicPass);
            }

            $start = microtime(true);
            $response = $httpClient->post($url, $payload);
            $elapsed = round((microtime(true) - $start) * 1000, 2);

            $this->newLine();
            $this->info("=== RESPUESTA ({$elapsed}ms) ===");
            $this->line("HTTP Status: {$response->status()}");

            $body = $response->body();
            if (!empty($body)) {
                $decoded = json_decode($body, true);
                if ($decoded !== null) {
                    $this->line("Body:");
                    $this->line(json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                } else {
                    $this->line("Body (raw): {$body}");
                }
            } else {
                $this->line("Body: (vacio)");
            }

            $this->newLine();

            if ($response->successful()) {
                $this->info('✅ AUTENTICACIÓN EXITOSA - El webhook respondió con 2xx');
                return 0;
            } elseif ($response->status() === 401) {
                $this->error('❌ AUTENTICACIÓN FALLIDA - HTTP 401 (Credenciales inválidas)');
                return 1;
            } elseif ($response->status() === 404) {
                $this->warn('⚠️  HTTP 404 - El webhook no está registrado o no está activo.');
                $this->warn('   En modo test (webhook-test/): Abre el workflow en n8n,');
                $this->warn('   haz clic en "Execute Workflow" y luego ejecuta este comando.');
                return 1;
            } elseif ($response->status() === 500) {
                $this->error('❌ ERROR INTERNO - HTTP 500 en el servidor de n8n');
                return 1;
            } else {
                $this->warn("⚠️  Código inesperado: {$response->status()}");
                return 1;
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $this->error('❌ ERROR DE CONEXIÓN: ' . $e->getMessage());
            $this->warn('   Verifica que la URL sea correcta y que tengas acceso a internet.');

            if (str_contains($e->getMessage(), 'SSL')) {
                $this->warn('   💡 Sugerencia: Agrega --insecure al comando si es un entorno de prueba local.');
            }

            return 1;
        } catch (\Throwable $e) {
            $this->error('❌ ERROR: ' . $e->getMessage());
            Log::error('Error en comando notify:n8n-test', [
                'message' => $e->getMessage(),
                'url'     => $url,
            ]);
            return 1;
        }
    }
}
