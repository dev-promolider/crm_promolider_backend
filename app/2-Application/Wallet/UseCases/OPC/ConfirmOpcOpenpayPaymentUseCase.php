<?php
namespace Promolider\Application\Wallet\UseCases\OPC;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Payment;
use Carbon\Carbon;
use Promolider\Domain\Registration\Ports\Out\PaymentGatewayInterface;
use App\Models\Product;
use App\Services\MLM\AffiliationRewardsService;

class ConfirmOpcOpenpayPaymentUseCase
{
    public function __construct(
        private PaymentGatewayInterface $paymentGateway
    ) {}

    /**
     * Confirma el pago de Openpay y aplica los cambios.
     */
    public function execute(string $chargeId): array
    {
        // 1. Verificar idempotencia temprana — si ya fue procesado, salir de inmediato
        $existingPayment = Payment::where('operation_number', $chargeId)->first();
        if ($existingPayment) {
            Log::info('[CONFIRM OPC] Pago ya procesado anteriormente (idempotencia)', ['charge_id' => $chargeId]);
            return ['success' => true, 'message' => 'El pago ya fue procesado anteriormente.'];
        }

        // 2. Obtener la información del charge en Openpay
        Log::info('[CONFIRM OPC] Consultando cargo en Openpay', ['charge_id' => $chargeId]);
        $chargeInfo = $this->paymentGateway->getCharge($chargeId);

        Log::info('[CONFIRM OPC] Respuesta de Openpay', [
            'charge_id' => $chargeId,
            'status' => $chargeInfo['status'],
            'amount' => $chargeInfo['amount'],
            'order_id' => $chargeInfo['order_id'] ?? 'NULL',
        ]);

        if ($chargeInfo['status'] !== 'completed') {
            throw new Exception("El pago en Openpay no está completado. Estado: " . $chargeInfo['status'], 400);
        }

        $orderId = $chargeInfo['order_id'] ?? null;

        // 3. Intentar obtener la intención de pago del cache
        $intent = null;
        if ($orderId) {
            $intent = Cache::get('opc_intent_' . $orderId);
        }
        // Fallback: buscar por chargeId (código legado)
        if (!$intent) {
            $intent = Cache::get('opc_intent_' . $chargeId);
        }

        // 4. Fallback final: reconstruir intención desde el order_id (opc-{userId}-{timestamp})
        //    Esto cubre el caso donde el server se reinició y perdió el cache.
        //    ES SEGURO porque los datos (userId, monto) vienen directamente de Openpay, no del cliente.
        if (!$intent && $orderId && preg_match('/^opc-(\d+)-(\d+)$/', $orderId, $matches)) {
            $userIdFromOrder = (int) $matches[1];
            $amountFromOpenpay = (float) $chargeInfo['amount'];

            // El precio de la cuota es el del producto OPC de su membresía, no $30 fijos.
            $usuarioOrden = User::find($userIdFromOrder);
            $productoOrden = $usuarioOrden
                ? Product::where('name', 'opc')->where('account_type_id', $usuarioOrden->id_account_type)->first()
                : null;
            $precioCuota = $productoOrden ? (float) $productoOrden->price : 0.0;

            if ($precioCuota <= 0) {
                Log::error('[CONFIRM OPC] No se puede reconstruir la intención: sin precio de OPC', [
                    'order_id' => $orderId,
                    'user_id'  => $userIdFromOrder,
                ]);
                throw new Exception("No se pudo determinar el precio del OPC para la orden: {$orderId}", 422);
            }

            $cuotasFromAmount = (int) round($amountFromOpenpay / $precioCuota);

            Log::warning('[CONFIRM OPC] Cache de intención no encontrado. Reconstruyendo desde order_id.', [
                'order_id'     => $orderId,
                'user_id'      => $userIdFromOrder,
                'amount'       => $amountFromOpenpay,
                'cuotas_calc'  => $cuotasFromAmount,
            ]);

            if ($cuotasFromAmount >= 1) {
                $intent = [
                    'user_id'    => $userIdFromOrder,
                    'cuotas'     => $cuotasFromAmount,
                    'amount'     => number_format($amountFromOpenpay, 2, '.', ''),
                    'order_id'   => $orderId,
                    'recovered'  => true, // Marcar como recuperado para auditoría
                ];
            }
        }

        if (!$intent) {
            Log::error('[CONFIRM OPC] No se pudo reconstruir la intención de pago.', [
                'charge_id' => $chargeId,
                'order_id'  => $orderId,
            ]);
            throw new Exception("Intención de pago no encontrada o expirada para la orden: {$orderId}", 404);
        }

        $userId        = $intent['user_id'];
        $cuotasPagadas = $intent['cuotas'];
        $expectedAmount = $intent['amount'];

        // 5. Verificar que el monto sea correcto (anti-fraude)
        if (abs((float)$chargeInfo['amount'] - (float)$expectedAmount) > 0.01) {
            Log::critical('[CONFIRM OPC] Discrepancia de monto — posible fraude', [
                'charge_id' => $chargeId,
                'expected'  => $expectedAmount,
                'real'      => $chargeInfo['amount'],
            ]);
            throw new Exception("El monto pagado no coincide con las cuotas solicitadas.", 400);
        }

        try {
            DB::beginTransaction();

            // 6. Bloqueo atómico para evitar race conditions (Doble clic)
            $user = User::where('id', $userId)->lockForUpdate()->first();

            if (!$user) {
                throw new Exception("Usuario no encontrado: {$userId}", 404);
            }

            // 7. Lógica estricta de Fechas
            $oldExpiration = Carbon::parse($user->expiration_date);
            $newExpiration = $oldExpiration->copy()->addMonths($cuotasPagadas);
            
            // Limitamos a la fecha de membresía para no sobrepasar los 12 meses
            $membershipExpiration = Carbon::parse($user->expiration_membership_date);
            if ($newExpiration->greaterThan($membershipExpiration)) {
                $newExpiration = $membershipExpiration;
            }

            // Actualizamos al usuario
            $user->expiration_date = $newExpiration;
            $user->save();

            // 8. Registro en Payments (igual al formato existente de recompras OPC)
            $payment = new Payment();
            $payment->user_id = $user->id;
            $payment->id_user_sponsor = $user->id_referrer_sponsor;
            $payment->amount = $expectedAmount;
            $payment->operation_number = $chargeId; // ID de transacción de Openpay
            $payment->id_payment_method = 1; // Tarjeta de crédito/débito
            $payment->details = 'Recompra de OPC';
            $payment->save();

            // 9. Repartir Puntos en la Red
            $this->distributePoints($user, $cuotasPagadas);

            DB::commit();

            // Limpiar la caché
            if ($orderId) {
                Cache::forget('opc_intent_' . $orderId);
            }
            Cache::forget('opc_intent_' . $chargeId);

            Log::info('[CONFIRM OPC] Pago OPC confirmado exitosamente', [
                'user_id'        => $userId,
                'charge_id'      => $chargeId,
                'cuotas'         => $cuotasPagadas,
                'nueva_fecha'    => $newExpiration->format('Y-m-d'),
            ]);

            return [
                'success'        => true,
                'message'        => "Mantenimiento OPC de {$cuotasPagadas} cuota(s) aplicado correctamente.",
                'new_expiration' => $newExpiration->format('Y-m-d'),
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[CONFIRM OPC] Error al procesar recompra OPC', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Reparte los puntos de la recompra por la red.
     *
     * Esto era un esbozo que solo dejaba una línea en el log: quien pagaba su OPC con
     * tarjeta no alimentaba la red, mientras que pagándolo con billetera sí. Ahora las
     * dos vías usan el mismo servicio y el mismo valor en puntos del producto.
     */
    private function distributePoints(User $user, int $cuotas)
    {
        $product = Product::where('name', 'opc')
            ->where('account_type_id', $user->id_account_type)
            ->first();

        if (!$product) {
            Log::warning('[CONFIRM OPC] Sin producto OPC para la membresía, no se reparten puntos', [
                'user_id'         => $user->id,
                'id_account_type' => $user->id_account_type,
            ]);
            return;
        }

        $puntos = (float) $product->points * $cuotas;
        $filas = app(AffiliationRewardsService::class)->distributeRepurchasePoints($user->id, $puntos);

        Log::info('[CONFIRM OPC] Puntos de recompra repartidos', [
            'user_id' => $user->id,
            'cuotas'  => $cuotas,
            'puntos'  => $puntos,
            'filas'   => $filas,
        ]);
    }
}
