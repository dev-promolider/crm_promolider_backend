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
        // 1. Obtener la intención de pago segura guardada previamente
        $intent = Cache::get('opc_intent_' . $chargeId);
        if (!$intent) {
            throw new Exception("Intención de pago no encontrada o expirada para el Charge ID: {$chargeId}", 404);
        }

        $userId = $intent['user_id'];
        $cuotasPagadas = $intent['cuotas'];
        $expectedAmount = $intent['amount'];

        // 2. Verificar en Openpay que el pago fue exitoso y por el monto correcto
        $chargeInfo = $this->paymentGateway->getCharge($chargeId);
        if ($chargeInfo['status'] !== 'completed') {
            throw new Exception("El pago en Openpay no está completado. Estado: " . $chargeInfo['status'], 400);
        }
        
        if ((float)$chargeInfo['amount'] !== (float)$expectedAmount) {
            Log::critical("Intento de fraude OPC", ['charge' => $chargeId, 'expected' => $expectedAmount, 'real' => $chargeInfo['amount']]);
            throw new Exception("El monto pagado no coincide con las cuotas solicitadas.", 400);
        }

        try {
            DB::beginTransaction();

            // 3. Bloqueo atómico para evitar race conditions (Doble clic)
            $user = User::where('id', $userId)->lockForUpdate()->first();
            
            // Verificar si el pago ya fue procesado (por idempotencia)
            $existingPayment = Payment::where('details->charge_id', $chargeId)->first();
            if ($existingPayment) {
                DB::rollBack();
                return ['success' => true, 'message' => 'El pago ya fue procesado anteriormente.'];
            }

            // 4. Lógica estricta de Fechas (La parte importante)
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

            // 5. Dejamos el registro exacto en Payments (Audit Trail JSON)
            $payment = new Payment();
            $payment->user_id = $user->id;
            $payment->id_user_sponsor = $user->id_referrer_sponsor;
            $payment->amount = $expectedAmount;
            $payment->operation_number = 5; // Openpay / Card
            $payment->id_payment_method = 1; // 1 = Openpay? (Ajustar según catálogo)
            $payment->details = json_encode([
                'type' => 'opc_repurchase',
                'charge_id' => $chargeId,
                'cuotas_pagadas' => $cuotasPagadas,
                'fecha_anterior' => $oldExpiration->toDateTimeString(),
                'nueva_fecha' => $newExpiration->toDateTimeString(),
                'openpay_auth' => $chargeInfo['authorization'] ?? null
            ]);
            $payment->save();

            // 6. Repartir Puntos en la Red (Lógica existente abstraída)
            $this->distributePoints($user, $cuotasPagadas);

            DB::commit();

            // Limpiar la caché de intención
            Cache::forget('opc_intent_' . $chargeId);

            return [
                'success' => true,
                'message' => "Mantenimiento OPC de {$cuotasPagadas} cuota(s) aplicado correctamente.",
                'new_expiration' => $newExpiration->format('Y-m-d')
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error al procesar recompra OPC Hexagonal", ['error' => $e->getMessage()]);
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
