<?php
namespace Promolider\Application\Marketing\UseCases\PurchasedCourses;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Promolider\Domain\Registration\Ports\Out\PaymentGatewayInterface;
use App\Models\User;

class ConfirmCourseOpenpayPaymentUseCase
{
    public function __construct(
        private PaymentGatewayInterface $paymentGateway,
        private StorePurchasedCourseUseCase $storePurchasedCourseUseCase
    ) {}

    /**
     * Confirma el pago de un curso mediante Openpay y asigna el curso.
     * @param string $chargeId
     * @return array
     */
    public function execute(string $chargeId): array
    {
        try {
            // Verificar el estado del cobro en Openpay
            $charge = $this->paymentGateway->getCharge($chargeId);
            $orderId = $charge['order_id'] ?? null;
            $status = $charge['status'] ?? null;

            if ($status !== 'completed') {
                throw new Exception("El pago en Openpay aún no está completado. Estado: {$status}", 400);
            }

            // Recuperar la intención de pago desde la caché
            $intentKey = 'course_purchase_intent_' . $orderId;
            $intentData = Cache::get($intentKey);

            if (!$intentData) {
                // Es posible que el webhook ya haya procesado esto o haya expirado.
                // Verificaremos si el curso ya fue asignado.
                Log::warning("Intención de compra de curso no encontrada en caché para order_id: {$orderId}. Intentando resolver desde descripción de Openpay.");
                throw new Exception("La sesión de pago ha expirado o ya fue procesada.", 400);
            }

            $userId = $intentData['user_id'];
            $courseId = $intentData['course_id'];
            
            // Registrar la compra llamando al caso de uso existente
            $result = $this->storePurchasedCourseUseCase->execute($userId, $courseId);

            // Lo que genera la venta: comisión del creador, comisión de quien recomendó y
            // PV para la red. Hasta ahora comprar un curso no generaba nada de esto. Si el
            // reparto falla, la compra ya está hecha: se registra el error y no se le
            // quita el curso al comprador.
            try {
                app(\App\Services\MLM\CoursePurchaseRewardsService::class)->repartir(
                    (int) $userId,
                    (int) $courseId,
                    (float) ($charge['amount'] ?? $intentData['amount'] ?? 0)
                );
            } catch (\Throwable $e) {
                Log::error('[COMPRA DE CURSO] No se pudieron repartir los premios: ' . $e->getMessage(), [
                    'user_id'   => $userId,
                    'course_id' => $courseId,
                ]);
            }

            // Eliminar la intención de caché para evitar doble procesamiento
            Cache::forget($intentKey);

            return [
                'success' => true,
                'message' => 'El curso ha sido comprado exitosamente',
                'course_id' => $courseId
            ];

        } catch (Exception $e) {
            Log::error('Error confirming Openpay course purchase: ' . $e->getMessage());
            throw $e;
        }
    }
}
