<?php
namespace Promolider\Application\Wallet\UseCases\OPC;

use Exception;
use Illuminate\Support\Facades\Cache;
use Promolider\Domain\Registration\Ports\Out\PaymentGatewayInterface;
use App\Models\User;
use App\Models\Product;

class InitOpcOpenpayPaymentUseCase
{
    public function __construct(
        private PaymentGatewayInterface $paymentGateway
    ) {}

    /**
     * Inicializa el pago OPC con Openpay.
     * Crea un intento de pago seguro (intención) y devuelve la URL para 3D Secure.
     */
    public function execute(int $userId, int $cuotasRequested): array
    {
        if ($cuotasRequested < 1) {
            throw new Exception("Debes pagar al menos 1 cuota.", 422);
        }

        $user = User::find($userId);
        if (!$user) {
            throw new Exception("Usuario no encontrado", 404);
        }

        // 1. Validar regla de negocio: ¿Está la membresía activa y vigente para aceptar pagos OPC?
        // En Promolider, si la membresía caducó, no pueden pagar OPC, deben comprar membresía nueva.
        if (now()->greaterThan($user->expiration_membership_date)) {
            throw new Exception("Tu membresía anual ha vencido. Por favor renueva tu membresía para reintegrarte al sistema.", 403);
        }

        // 2. Obtener producto OPC según la membresía del usuario
        $product = Product::where('name', 'opc')
            ->where('account_type_id', $user->id_account_type)
            ->first();

        if (!$product) {
            throw new Exception("No existe un producto OPC asociado a tu membresía.", 404);
        }

        // 3. Calcular monto fijo de $30.00 por cuota (regla de negocio solicitada)
        $amountPerQuota = 30.00;
        $totalAmount = $amountPerQuota * $cuotasRequested;
        $totalAmountFormatted = number_format($totalAmount, 2, '.', '');

        // 4. Generar Order ID y Redirección
        $orderNumber = time();
        $orderId = substr('opc-' . $userId . '-' . $orderNumber, 0, 100);
        $redirectUrl = env('FRONTEND_URL', 'http://localhost:5173') . '/dashboard/mis-compras?payment=success_opc'; // Redirigir al dashboard con flag de éxito

        // 5. Configurar Link de Checkout en Openpay
        $checkoutData = [
            'amount'      => (float) $totalAmountFormatted,
            'description' => "Recompra OPC - {$cuotasRequested} cuotas",
            'order_id'    => $orderId,
            'currency'    => 'USD', // Openpay PE requiere PEN o USD según configuración
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

        // 6. Guardar la Intención de Pago (Seguridad Hexagonal contra manipulación)
        // Guardamos en caché o BD temporal para validarlo en el Webhook/Confirmación
        Cache::put('opc_intent_' . $orderId, [
            'user_id' => $userId,
            'cuotas'  => $cuotasRequested,
            'amount'  => $totalAmountFormatted,
            'product_id' => $product->id,
            'order_id' => $orderId
        ], now()->addMinutes(60)); // Intención válida por 1 hora

        return [
            'payment_url' => $chargeResult['payment_url'],
            'charge_id'   => $chargeResult['charge_id'],
            'order_id'    => $orderId,
        ];
    }
}
