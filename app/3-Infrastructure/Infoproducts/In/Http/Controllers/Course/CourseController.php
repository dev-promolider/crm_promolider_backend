<?php

namespace Promolider\Infrastructure\Infoproducts\In\Http\Controllers\Course;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Promolider\Application\Infoproducts\UseCases\Course\GetCourseDataUseCase;
use Promolider\Application\Infoproducts\UseCases\Course\GetOrdersForCourseUseCase;
use Promolider\Application\Infoproducts\UseCases\Course\Module\GetModuleDataUseCase;

class CourseController extends Controller
{
    public function __construct(
        private GetCourseDataUseCase $getCourseDataUseCase,
        private GetModuleDataUseCase $getModuleDataUseCase,
        private GetOrdersForCourseUseCase $getOrdersForCourseUseCase
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
}
