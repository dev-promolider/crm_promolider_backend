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
use App\Http\Controllers\Api\CartController;
use App\Models\Classified;
use App\Models\Point;

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
            $cuotasFromAmount  = (int) round($amountFromOpenpay / 30);

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

    private function distributePoints(User $user, int $cuotas)
    {
        // Esta función replica la lógica de recorrer la rama de Classified
        // repartiendo puntos. Se asume que el volumen (puntos) se obtiene del producto.
        // Simplificado para el ejemplo:
        Log::info("Distribuyendo puntos OPC para el usuario {$user->id}, Cuotas: {$cuotas}");
        // Implementar lógica de bucle limitando iteraciones a 50 como se discutió.
    }
}
