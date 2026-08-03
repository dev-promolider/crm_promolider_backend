<?php

namespace Promolider\Infrastructure\VCR\In\Http\Controllers;

use App\Models\Category;
use App\Models\Course;
use App\Models\Notifications;
use App\Models\PurchasedCourse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class VcrCartController extends Controller
{
    /**
     * GET /api/v1/category/list
     */
    public function categoryList()
    {
        $categories = Category::select('id', 'name', 'icon')->get();

        return response()->json([
            'status' => 'ok',
            'message' => '',
            'data' => $categories,
        ]);
    }

    /**
     * GET /api/v1/cart/show
     */
    public function cartShow(Request $request)
    {
        $sessionCart = $request->session()->get('cart', []);

        return response()->json([
            'status' => 'ok',
            'data' => $sessionCart,
        ]);
    }

    /**
     * GET /api/v1/cart/add/{course}
     */
    public function cartAdd(Request $request, $courseId)
    {
        $course = Course::find($courseId);
        if (!$course) {
            return response()->json(['status' => 'error', 'message' => 'Curso no encontrado'], 404);
        }

        $cart = $request->session()->get('cart', []);
        $cart[$courseId] = [
            'id' => $course->id,
            'title' => $course->title,
            'price' => $course->price,
        ];
        $request->session()->put('cart', $cart);

        return response()->json([
            'status' => 'ok',
            'message' => 'Curso agregado al carrito',
            'data' => $cart,
        ]);
    }

    /**
     * POST /api/v1/cart/buy-course
     *
     * Seguridad (CRM-03): NO crea la compra sin verificar el pago.
     * Exige un order_id de PayPal y lo verifica contra la API de PayPal
     * antes de crear el PurchasedCourse. Si no hay credenciales PayPal
     * configuradas, rechaza la operación (fail-closed).
     */
    public function buyCourse(Request $request)
    {
        $userId = auth()->id();
        $courseId = $request->input('id_course');
        $orderId = $request->input('order_id');

        if (!$orderId) {
            return response()->json(['status' => 'error', 'message' => 'order_id es requerido para verificar el pago.'], 422);
        }

        $existing = PurchasedCourse::where('user_id', $userId)->where('course_id', $courseId)->first();
        if ($existing) {
            return response()->json(['status' => 'ok', 'message' => 'Ya estás inscrito en este curso', 'data' => $existing]);
        }

        $course = Course::find($courseId);
        if (!$course) {
            return response()->json(['status' => 'error', 'message' => 'Curso no encontrado'], 404);
        }

        // Verificar la orden contra PayPal (fail-closed)
        $paymentVerified = $this->verifyPaypalOrder($orderId, (float) $course->price);
        if ($paymentVerified !== true) {
            \Illuminate\Support\Facades\Log::warning('PayPal: compra rechazada, orden no verificada', [
                'user_id' => $userId,
                'course_id' => $courseId,
                'order_id' => $orderId,
                'reason' => is_string($paymentVerified) ? $paymentVerified : 'unknown',
            ]);
            return response()->json(['status' => 'error', 'message' => 'No se pudo verificar el pago con PayPal.'], 402);
        }

        $purchased = PurchasedCourse::create([
            'user_id' => $userId,
            'course_id' => $courseId,
            'display_time' => 0,
            'last_class_reprod' => 0,
            'classes_status' => json_encode([]),
            'completed_course' => 0,
        ]);

        Notifications::create([
            'id_generator' => $userId,
            'id_receiver' => $course->user_id,
            'title' => 'Nueva venta de curso',
            'body' => 'Un estudiante se ha inscrito en tu curso: ' . $course->title,
            'type' => 'VENTA_CURSO',
        ]);

        return response()->json([
            'status' => 'ok',
            'message' => 'Compra realizada con éxito',
            'data' => $purchased,
        ]);
    }

    /**
     * Verifica una orden de PayPal capturando el access token y consultando /v2/checkout/orders/{id}.
     * Devuelve true si la orden está aprobada/completada y el monto coincide.
     * Devuelve un string con el motivo si falla. Falla cerrado si no hay credenciales.
     */
    protected function verifyPaypalOrder(string $orderId, float $expectedAmount)
    {
        $clientId = env('PAYPAL_CLIENT_ID');
        $clientSecret = env('PAYPAL_SECRET');
        $baseUri = env('PAYPAL_API_BASE', 'https://api-m.paypal.com');

        if (!$clientId || !$clientSecret) {
            return 'paypal_credentials_missing';
        }

        try {
            $client = new \GuzzleHttp\Client();

            $tokenRes = $client->post($baseUri . '/v1/oauth2/token', [
                'auth' => [$clientId, $clientSecret],
                'form_params' => ['grant_type' => 'client_credentials'],
                'http_errors' => false,
            ]);
            if ($tokenRes->getStatusCode() !== 200) {
                return 'paypal_token_failed';
            }
            $accessToken = json_decode((string) $tokenRes->getBody(), true)['access_token'] ?? null;
            if (!$accessToken) {
                return 'paypal_token_missing';
            }

            $orderRes = $client->get($baseUri . '/v2/checkout/orders/' . urlencode($orderId), [
                'headers' => ['Authorization' => 'Bearer ' . $accessToken],
                'http_errors' => false,
            ]);
            if ($orderRes->getStatusCode() !== 200) {
                return 'paypal_order_not_found';
            }
            $order = json_decode((string) $orderRes->getBody(), true);
            $status = $order['status'] ?? null;
            if ($status !== 'APPROVED' && $status !== 'COMPLETED') {
                return 'paypal_order_' . $status;
            }

            $amount = (float) ($order['purchase_units'][0]['amount']['value'] ?? 0);
            if ($amount <= 0 || abs($amount - $expectedAmount) > 0.01) {
                return 'paypal_amount_mismatch';
            }

            return true;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('PayPal verify error', ['error' => $e->getMessage()]);
            return 'paypal_exception';
        }
    }

    /**
     * POST /api/v1/pay/course-openpay
     */
    public function payCourseOpenpay(Request $request)
    {
        $userId = auth()->id();
        if (!$userId) {
            return response()->json(['status' => 'error', 'message' => 'No autenticado'], 401);
        }
        $user = User::find($userId);

        $cartItems = $request->input('cart_items', []);
        $totalAmount = $request->input('total_amount');

        if (empty($cartItems) && $request->has('id_course')) {
            $course = Course::find($request->input('id_course'));
            if ($course) {
                $cartItems = [
                    [
                        'id' => $course->id,
                        'title' => $course->title,
                        'price' => $course->price,
                    ]
                ];
            }
        }

        if (empty($cartItems)) {
            return response()->json(['status' => 'error', 'message' => 'El carrito está vacío'], 400);
        }

        if (!$totalAmount) {
            $totalAmount = array_reduce($cartItems, function ($sum, $item) {
                return $sum + (float)($item['price'] ?? 0);
            }, 0);
        }

        $openpayId     = config('services.openpay.id');
        $openpaySecret = config('services.openpay.sk');

        $courseTitles = array_map(function ($item) {
            return $item['title'] ?? 'Curso';
        }, $cartItems);
        $description = 'Compra de cursos VCR: ' . implode(', ', $courseTitles);
        if (strlen($description) > 240) {
            $description = substr($description, 0, 237) . '...';
        }

        $redirectUrl = env('VUE_APP_FRONT_URL', 'http://localhost:8081') . '/suscription-user';

        if (!empty($openpayId) && !empty($openpaySecret)) {
            try {
                $openpay = \Openpay\Data\Openpay::getInstance($openpayId, $openpaySecret, 'PE', $request->ip());
                \Openpay\Data\Openpay::setProductionMode(config('services.openpay.production_mode', false));

                $chargeData = [
                    'method' => 'card',
                    'amount' => (float)$totalAmount,
                    'currency' => 'USD',
                    'description' => $description,
                    'confirm' => 'false',
                    'send_email' => 'false',
                    'redirect_url' => $redirectUrl,
                    'customer' => [
                        'name' => $user ? $user->name : 'Estudiante',
                        'last_name' => $user ? ($user->lastname ?? $user->name) : 'Promolider',
                        'phone_number' => $user->phone ?? '999999999',
                        'email' => $user ? $user->email : 'estudiante@promolider.org',
                    ],
                ];

                $charge = $openpay->charges->create($chargeData);

                return response()->json([
                    'status' => 'ok',
                    'message' => 'Orden de Openpay generada exitosamente',
                    'payment_url' => $charge->payment_method->url,
                    'charge_id' => $charge->id,
                    'data' => [
                        'payment_url' => $charge->payment_method->url,
                        'charge_id' => $charge->id,
                    ],
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error generando cobro Openpay VCR', ['error' => $e->getMessage()]);
            }
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'Redirección a pasarela Openpay',
            'payment_url' => $redirectUrl,
        ]);
    }

    /**
     * POST /api/v1/marketing/toggleMarketplaceViewability/{courseId}
     */
    public function toggleMarketplaceViewability($courseId)
    {
        $course = Course::find($courseId);
        if (!$course) {
            return response()->json(['status' => 'error', 'message' => 'Curso no encontrado'], 404);
        }

        if ((int) $course->user_id !== (int) auth()->id()) {
            return response()->json(['status' => 'error', 'message' => 'No autorizado'], 403);
        }

        $course->marketplace_listed = $course->marketplace_listed == 1 ? 0 : 1;
        $course->save();

        return response()->json([
            'data' => (bool) $course->marketplace_listed,
            'message' => 'Actualizado con éxito.',
        ]);
    }
}
