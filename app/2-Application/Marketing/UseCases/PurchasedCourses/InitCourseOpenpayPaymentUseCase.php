<?php
namespace Promolider\Application\Marketing\UseCases\PurchasedCourses;

use Exception;
use Illuminate\Support\Facades\Cache;
use Promolider\Domain\Registration\Ports\Out\PaymentGatewayInterface;
use App\Models\User;
use App\Models\Course;

class InitCourseOpenpayPaymentUseCase
{
    public function __construct(
        private PaymentGatewayInterface $paymentGateway
    ) {}

    /**
     * Inicializa la compra de un curso con Openpay.
     * Crea un intento de pago seguro (intención) y devuelve la URL para 3D Secure.
     */
    public function execute(int $userId, int $courseId, string $frontendUrl): array
    {
        $user = User::find($userId);
        if (!$user) {
            throw new Exception("Usuario no encontrado", 404);
        }

        $course = Course::where('id', $courseId)->first();
        if (!$course) {
            throw new Exception("Curso no encontrado", 404);
        }

        $amount = (float) ($course->price > 0 ? $course->price : $course->price_base);
        
        if ($amount <= 0) {
            throw new Exception("El curso no tiene precio o es gratuito.", 422);
        }

        $amountFormatted = number_format($amount, 2, '.', '');

        // Generar Order ID
        $orderNumber = time();
        $orderId = substr('course-' . $userId . '-' . $courseId . '-' . $orderNumber, 0, 100);
        
        // La URL de redirección será la página del curso en el VCR con un flag
        $redirectUrl = rtrim($frontendUrl, '/') . "?payment_course=success";

        // Remover caracteres especiales y acentos de la descripción para evitar el error 422 de OpenPay
        $cleanTitle = preg_replace('/[^a-zA-Z0-9\s]/', '', iconv('UTF-8', 'ASCII//TRANSLIT', $course->title));
        $description = "Compra de Curso " . substr(trim($cleanTitle), 0, 80);

        // Configurar Link de Checkout en Openpay
        $checkoutData = [
            'amount'      => (float) $amountFormatted,
            'description' => $description,
            'order_id'    => $orderId,
            'currency'    => 'USD',
            'redirect_url' => $redirectUrl,
            'customer'    => [
                'name'         => $user->name ?? 'Usuario',
                'last_name'    => $user->last_name ?? 'Promolider',
                'phone_number' => $user->phone ?? '999999999',
                'email'        => $user->email,
            ],
            'send_email'   => false,
        ];

        $chargeResult = $this->paymentGateway->createCheckoutLink($checkoutData);

        // Guardar la Intención de Pago en caché
        Cache::put('course_purchase_intent_' . $orderId, [
            'user_id' => $userId,
            'course_id' => $courseId,
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
