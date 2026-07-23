<?php
namespace Promolider\Application\Wallet\UseCases;

use Exception;
use Illuminate\Support\Facades\Cache;
use Promolider\Domain\Registration\Ports\Out\PaymentGatewayInterface;
use App\Models\User;

class InitRechargeOpenpayPaymentUseCase
{
    public function __construct(
        private PaymentGatewayInterface $paymentGateway
    ) {}

    /**
     * Inicializa la recarga de billetera con Openpay.
     * Crea un intento de pago seguro (intención) y devuelve la URL para 3D Secure.
     */
    public function execute(int $userId, float $amount): array
    {
        if ($amount <= 0) {
            throw new Exception("El monto a recargar debe ser mayor a 0.", 422);
        }

        $user = User::find($userId);
        if (!$user) {
            throw new Exception("Usuario no encontrado", 404);
        }

        $amountFormatted = number_format($amount, 2, '.', '');

        // Generar Order ID y Redirección
        $orderNumber = time();
        $orderId = substr('recharge-' . $userId . '-' . $orderNumber, 0, 100);
        $redirectUrl = config('app.frontend_url') . '/dashboard/billetera?payment=success_recharge';

        // Configurar Link de Checkout en Openpay
        $checkoutData = [
            'amount'      => (float) $amountFormatted,
            'description' => "Recarga de Billetera",
            'order_id'    => $orderId,
            'currency'    => 'USD',
            'redirect_url' => $redirectUrl,
            'customer'    => [
                'name'         => $user->name,
                'last_name'    => $user->last_name,
                'phone_number' => $user->phone,
                'email'        => $user->email,
            ],
            'send_email'   => false,
        ];

        $chargeResult = $this->paymentGateway->createCheckoutLink($checkoutData);

        // Guardar la Intención de Pago en caché
        Cache::put('wallet_recharge_intent_' . $orderId, [
            'user_id' => $userId,
            'amount'  => $amountFormatted,
            'order_id' => $orderId
        ], now()->addMinutes(60)); // Intención válida por 1 hora

        return [
            'payment_url' => $chargeResult['payment_url'],
            'charge_id'   => $chargeResult['charge_id'],
            'order_id'    => $orderId,
        ];
    }
}
