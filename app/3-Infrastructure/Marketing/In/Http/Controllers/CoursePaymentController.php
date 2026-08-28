<?php
namespace Promolider\Infrastructure\Marketing\In\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Promolider\Application\Marketing\UseCases\PurchasedCourses\InitCourseOpenpayPaymentUseCase;
use Promolider\Application\Marketing\UseCases\PurchasedCourses\ConfirmCourseOpenpayPaymentUseCase;

class CoursePaymentController extends Controller
{
    public function __construct(
        private InitCourseOpenpayPaymentUseCase $initCourseOpenpayPaymentUseCase,
        private ConfirmCourseOpenpayPaymentUseCase $confirmCourseOpenpayPaymentUseCase
    ) {}

    /**
     * POST /pay/course-openpay
     * Inicia la compra de un curso con Openpay.
     */
    public function openpay(Request $request)
    {
        try {
            $data = $request->validate([
                'course_id' => 'required|numeric|min:1',
                'redirect_url' => 'nullable|string|url',
            ]);

            $userId = $request->user()->id;
            
            $frontendUrl = $data['redirect_url'] ?? $request->headers->get('referer') ?? config('app.frontend_url');

            $result = $this->initCourseOpenpayPaymentUseCase->execute(
                $userId, 
                (int) $data['course_id'], 
                $frontendUrl
            );

            return response()->json([
                'status' => 'ok',
                'payment_url' => $result['payment_url'],
                'order_id' => $result['order_id']
            ]);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('OpenPay Course Purchase Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            $status = $e->getCode() >= 100 && $e->getCode() <= 599 ? $e->getCode() : 500;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }

    /**
     * POST /pay/course-confirm-openpay
     * Confirma la compra del curso luego del redirect de Openpay.
     */
    public function confirmOpenpay(Request $request)
    {
        try {
            $data = $request->validate([
                'id' => 'required|string', // Este es el charge_id de Openpay
            ]);

            $result = $this->confirmCourseOpenpayPaymentUseCase->execute($data['id']);

            return response()->json([
                'status' => 'ok',
                'message' => $result['message'],
                'course_id' => $result['course_id'] ?? null
            ]);
            
        } catch (\Exception $e) {
            $status = $e->getCode() >= 100 && $e->getCode() <= 599 ? $e->getCode() : 500;
            return response()->json(['error' => $e->getMessage()], $status);
        }
    }
}
