<?php

namespace Promolider\Infrastructure\Infoproducts\In\Http\Controllers\Course;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Promolider\Application\Infoproducts\UseCases\Course\StoreCourseConfigurationUseCase;
use Promolider\Application\Infoproducts\UseCases\Course\GetCourseConfigurationDataUseCase;

class CourseConfigurationController extends Controller
{
    public function __construct(
        private StoreCourseConfigurationUseCase $storeCourseConfigurationUseCase,
        private GetCourseConfigurationDataUseCase $getCourseConfigurationDataUseCase
    ) {}

    public function store(Request $request)
    {
        $storeResponse = $this->storeCourseConfigurationUseCase->execute($request->all());

        if (!$storeResponse['success']) {
            return response()->json([
                'success' => false,
                'message' => $storeResponse['message'],
            ], 400);
        }

        return response()->json([
            'success' => $storeResponse['success'],
            'message' => $storeResponse['message'],
        ], 200);
    }

    public function getConfigureCertificate(Request $request)
    {
        $courseId = $request->route('courseId');

        $response = $this->getCourseConfigurationDataUseCase->execute($courseId);

        if (!$response['success']) {
            return response()->json([
                'success' => false,
                'message' => $response['message'],
            ], 400);
        }

        return response()->json([
            'success' => $response['success'],
            'data' => $response['data'] ?? [],
            'message' => $response['message'] ?? 'Configuration retrieved successfully.',
        ], 200);
    }
}
