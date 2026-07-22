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
        // 1. Obtener la información del charge en Openpay para extraer el order_id
        $chargeInfo = $this->paymentGateway->getCharge($chargeId);
        if ($chargeInfo['status'] !== 'completed') {
            throw new Exception("El pago en Openpay no está completado. Estado: " . $chargeInfo['status'], 400);
        }

        $orderId = $chargeInfo['order_id'] ?? null;

        // 2. Obtener la intención de recarga guardada previamente
        $intent = Cache::get('wallet_recharge_intent_' . $orderId);
        
        if (!$intent) {
            $intent = Cache::get('wallet_recharge_intent_' . $chargeId);
        }

        if (!$intent) {
            throw new Exception("Intención de recarga no encontrada o expirada para la orden: {$orderId}", 404);
        }

        $userId = $intent['user_id'];
        $expectedAmount = $intent['amount'];

        // 3. Verificar que el monto sea correcto
        if ((float)$chargeInfo['amount'] !== (float)$expectedAmount) {
            Log::critical("Intento de fraude Recarga Wallet", ['charge' => $chargeId, 'expected' => $expectedAmount, 'real' => $chargeInfo['amount']]);
            throw new Exception("El monto pagado no coincide con el monto de recarga solicitado.", 400);
        }

        try {
            DB::beginTransaction();

            // Bloqueo atómico
            $user = User::where('id', $userId)->lockForUpdate()->first();
            
            // Idempotencia: Verificar si ya fue procesada la recarga
            $existingPayment = Payment::where('details->charge_id', $chargeId)->first();
            if ($existingPayment) {
                DB::rollBack();
                return ['success' => true, 'message' => 'La recarga ya fue procesada anteriormente.'];
            }

            // Asegurar que el usuario tenga una billetera
            $wallet = Wallet::firstOrCreate(['user_id' => $user->id]);

            // Agregar el movimiento a la billetera (Crédito)
            DB::table('wallet_movements')->insert([
                'wallet_id' => $wallet->id,
                'id_receiver' => $user->id,
                'id_payment' => null,
                'type' => 1, // 1 = Crédito (Ingreso)
                'amount' => $expectedAmount,
                'status' => 1, // Aprobado
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
            $payment->operation_number = "RECHARGE_" . time();
            $payment->id_payment_method = 1; // 1 = Openpay
            $payment->details = json_encode([
                'type' => 'wallet_recharge',
                'charge_id' => $chargeId,
                'openpay_auth' => $chargeInfo['authorization'] ?? null
            ]);
            $payment->save();

            DB::commit();

            // Limpiar la caché de intención
            Cache::forget('wallet_recharge_intent_' . $chargeId);
            Cache::forget('wallet_recharge_intent_' . $orderId);

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
