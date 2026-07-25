<?php
namespace Promolider\Application\Wallet\UseCases;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Payment;
use Promolider\Domain\Registration\Ports\Out\PaymentGatewayInterface;

class ConfirmRechargeOpenpayPaymentUseCase
{
    public function __construct(
        private PaymentGatewayInterface $paymentGateway
    ) {}

    /**
     * Confirma la recarga de la billetera con Openpay y aplica los fondos.
     */
    public function execute(string $chargeId): array
    {
        // 1. Verificar idempotencia temprana
        $existingPayment = Payment::where('operation_number', $chargeId)->first();
        if ($existingPayment) {
            Log::info('[CONFIRM RECHARGE] Recarga ya procesada anteriormente (idempotencia)', ['charge_id' => $chargeId]);
            return ['success' => true, 'message' => 'La recarga ya fue procesada anteriormente.'];
        }

        // 2. Obtener la información del charge en Openpay
        $chargeInfo = $this->paymentGateway->getCharge($chargeId);
        if ($chargeInfo['status'] !== 'completed') {
            throw new Exception("El pago en Openpay no está completado. Estado: " . $chargeInfo['status'], 400);
        }

        $orderId = $chargeInfo['order_id'] ?? null;

        // 3. Obtener la intención de recarga guardada previamente
        $intent = null;
        if ($orderId) {
            $intent = Cache::get('wallet_recharge_intent_' . $orderId);
        }
        
        if (!$intent) {
            $intent = Cache::get('wallet_recharge_intent_' . $chargeId);
        }

        // Fallback final: reconstruir intención desde el order_id (recharge-{userId}-{timestamp})
        if (!$intent && $orderId && preg_match('/^recharge-(\d+)-(\d+)$/', $orderId, $matches)) {
            $userIdFromOrder = (int) $matches[1];
            $amountFromOpenpay = (float) $chargeInfo['amount'];

            Log::warning('[CONFIRM RECHARGE] Cache de intención no encontrado. Reconstruyendo desde order_id.', [
                'order_id'     => $orderId,
                'user_id'      => $userIdFromOrder,
                'amount'       => $amountFromOpenpay,
            ]);

            $intent = [
                'user_id'    => $userIdFromOrder,
                'amount'     => number_format($amountFromOpenpay, 2, '.', ''),
                'order_id'   => $orderId,
                'recovered'  => true,
            ];
        }

        if (!$intent) {
            throw new Exception("Intención de recarga no encontrada o expirada para la orden: {$orderId}", 404);
        }

        $userId = $intent['user_id'];
        $expectedAmount = $intent['amount'];

        // 4. Verificar que el monto sea correcto
        if (abs((float)$chargeInfo['amount'] - (float)$expectedAmount) > 0.01) {
            Log::critical("Intento de fraude Recarga Wallet", ['charge' => $chargeId, 'expected' => $expectedAmount, 'real' => $chargeInfo['amount']]);
            throw new Exception("El monto pagado no coincide con el monto de recarga solicitado.", 400);
        }

        try {
            DB::beginTransaction();

            // Bloqueo atómico
            $user = User::where('id', $userId)->lockForUpdate()->first();
            
            // Asegurar que el usuario tenga una billetera
            $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);

            // Agregar el movimiento a la billetera (Crédito)
            DB::table('wallet_movements')->insert([
                'wallet_id' => $wallet->id,
                'id_receiver' => $user->id,
                'type' => 1, // 1 = Crédito (Ingreso)
                'amount' => $expectedAmount,
                'status' => 0, // 0 = Pendiente (requiere aprobación)
                'reason' => "Recarga de Billetera vía Openpay",
                'batch' => 0,
                'bonus_type_id' => null,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Guardar registro en Payments (Audit Trail)
            $payment = new Payment();
            $payment->user_id = $user->id;
            $payment->id_user_sponsor = $user->id_referrer_sponsor;
            $payment->amount = $expectedAmount;
            $payment->operation_number = $chargeId;
            $payment->id_payment_method = 1; // 1 = Openpay
            $payment->details = 'Recarga de Billetera OPC';
            $payment->save();

            DB::commit();

            // Limpiar la caché de intención
            Cache::forget('wallet_recharge_intent_' . $chargeId);
            if ($orderId) {
                Cache::forget('wallet_recharge_intent_' . $orderId);
            }

            return [
                'success' => true,
                'message' => "Recarga de {$expectedAmount} USD procesada exitosamente."
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error al procesar recarga de wallet Openpay", ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
