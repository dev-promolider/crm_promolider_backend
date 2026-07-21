<?php
namespace Promolider\Infrastructure\Registration\Out\Payment;

use Promolider\Domain\Registration\Ports\Out\PaymentGatewayInterface;
use App\Models\UnverifiedUser;
use Illuminate\Support\Facades\Log;

class OpenpayPaymentGateway implements PaymentGatewayInterface
{
    public function createCharge(array $chargeData): array
    {
        $openpayId     = config('services.openpay.id');
        $openpaySecret = config('services.openpay.sk');

        if (empty($openpayId) || empty($openpaySecret)) {
            throw new \Exception('Credenciales de Openpay no configuradas.', 500);
        }

        $openpay = \Openpay\Data\Openpay::getInstance($openpayId, $openpaySecret, 'PE', request()->ip());
        
        // Modo test — controlado por variable de entorno
        \Openpay\Data\Openpay::setProductionMode(
            config('services.openpay.production_mode', false)
        );

        $charge = $openpay->charges->create($chargeData);

        return [
            'payment_url' => $charge->payment_method->url,
            'charge_id'   => $charge->id,
        ];
    }

    public function createCheckoutLink(array $checkoutData): array
    {
        $openpayId     = config('services.openpay.id');
        $openpaySecret = config('services.openpay.sk');
        $isProduction  = config('services.openpay.production_mode', false);

        if (empty($openpayId) || empty($openpaySecret)) {
            throw new \Exception('Credenciales de Openpay no configuradas.', 500);
        }
        
        $baseUrl = $isProduction 
            ? "https://api.openpay.pe/v1/{$openpayId}/checkouts"
            : "https://sandbox-api.openpay.pe/v1/{$openpayId}/checkouts";
            
        $response = \Illuminate\Support\Facades\Http::asJson()->acceptJson()
            ->withBasicAuth($openpaySecret, '')
            ->post($baseUrl, $checkoutData);
            
        if ($response->failed()) {
            Log::error("Openpay Checkout Error: " . $response->body());
            throw new \Exception('Error al conectar con la pasarela de pago. ' . $response->json('description', ''), 500);
        }
        
        $result = $response->json();
        
        return [
            'payment_url' => $result['checkout_link'],
            'charge_id'   => $result['id'],
        ];
    }

    public function saveUnverifiedUser(array $userData): void
    {
        $unverified = new UnverifiedUser();
        $unverified->username        = $userData['username'];
        $unverified->password        = $userData['password'];
        $unverified->openpay_order_id = $userData['openpay_order_id'];
        $unverified->data            = json_encode($userData['data']);
        $unverified->save();

        Log::info('UnverifiedUser creado', [
            'username'  => $userData['username'],
            'charge_id' => $userData['openpay_order_id'],
        ]);
    }

    public function cleanPreviousUnverified(string $email): void
    {
        $deleted = UnverifiedUser::where(function ($query) use ($email) {
            // Optimización: Solo usamos LIKE para evitar timeout por JSON_EXTRACT
            $query->where('data', 'like', '%"email":"' . $email . '"%')
                  ->orWhere('data', 'like', '%"email": "' . $email . '"%');
        })->delete();

        if ($deleted > 0) {
            Log::info("Limpiados {$deleted} registros UnverifiedUser previos para {$email}");
        }
    }

    public function getCharge(string $chargeId): array
    {
        $openpayId     = config('services.openpay.id');
        $openpaySecret = config('services.openpay.sk');

        if (empty($openpayId) || empty($openpaySecret)) {
            throw new \Exception('Credenciales de Openpay no configuradas.', 500);
        }

        $openpay = \Openpay\Data\Openpay::getInstance($openpayId, $openpaySecret, 'PE', request()->ip());
        \Openpay\Data\Openpay::setProductionMode(config('services.openpay.production_mode', false));

        $charge = $openpay->charges->get($chargeId);

        return [
            'id' => $charge->id,
            'status' => $charge->status,
            'amount' => $charge->amount,
            'authorization' => $charge->authorization,
        ];
    }
}
