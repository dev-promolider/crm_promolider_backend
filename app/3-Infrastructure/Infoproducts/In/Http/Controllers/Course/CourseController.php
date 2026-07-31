<?php

namespace Promolider\Infrastructure\Infoproducts\In\Http\Controllers\Course;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Promolider\Application\Infoproducts\UseCases\Course\ChangeClassOrderUseCase;
use Promolider\Application\Infoproducts\UseCases\Course\GetCourseDataUseCase;
use Promolider\Application\Infoproducts\UseCases\Course\GetOrdersForCourseUseCase;
use Promolider\Application\Infoproducts\UseCases\Course\Module\GetModuleDataUseCase;
use Promolider\Application\Infoproducts\UseCases\Course\SubmitCourseForReviewUseCase;
use Promolider\Infrastructure\Infoproducts\In\Http\Requests\ChangeClassOrderRequest;

class CourseController extends Controller
{
    public function __construct(
        private GetCourseDataUseCase $getCourseDataUseCase,
        private GetModuleDataUseCase $getModuleDataUseCase,
        private GetOrdersForCourseUseCase $getOrdersForCourseUseCase,
        private SubmitCourseForReviewUseCase $submitCourseForReviewUseCase,
        private ChangeClassOrderUseCase $changeClassOrderUseCase
    ) {}

    public function show(Request $request)
    {
        $userId = $request->user()->id;
        $courseId = (int) $request->route('courseId');

        $courseData = $this->getCourseDataUseCase->execute($userId, $courseId);

        return response()->json($courseData);
    }

    public function modulesList(Request $request)
    {
        $userId = $request->user()->id;
        $courseId = (int) $request->route('courseId');

        $modules = $this->getModuleDataUseCase->execute($userId, $courseId);

        return response()->json($modules);
    }

    public function getOrders(Request $request)
    {
        $courseId = (int) $request->route('id');
        $orders = $this->getOrdersForCourseUseCase->execute($courseId);

        return response()->json($orders);
    }

    public function sendRequest(
        int $courseId,
        Request $request
    ): JsonResponse {
        $result = $this->submitCourseForReviewUseCase->execute(
            userId: (int) $request->user()->id,
            courseId: $courseId
        );

        $statusCode = $result === 'empty_files'
            ? 422
            : 200;

        return response()->json([
            'data' => $result,
            'message' => $result === 'ok'
                ? 'Solicitud de revisión enviada correctamente.'
                : 'No se pudo enviar la solicitud de revisión.',
        ], $statusCode);
    }

    public function changeOrder(
        ChangeClassOrderRequest $request
    ): JsonResponse {
        $validated = $request->validated();

        $order = $this->changeClassOrderUseCase->execute(
            userId: (int) $request->user()->id,
            courseId: (int) $validated['id'],
            items: $validated['order']
        );

        return response()->json([
            'data' => $order,
            'message' => 'Orden de las clases actualizado correctamente.',
        ]);
    }
}
