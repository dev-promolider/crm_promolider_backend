<?php

namespace Promolider\Infrastructure\Infoproducts\In\Http\Controllers\Course;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Promolider\Application\Infoproducts\UseCases\Course\Module\GetModuleDataUseCase;
use Promolider\Application\Infoproducts\UseCases\Course\UpdateModuleUseCase;
use Promolider\Infrastructure\Infoproducts\In\Http\Requests\Course\UpdateModuleRequest;

class CourseModuleController extends Controller
{
    public function __construct(
        private GetModuleDataUseCase $getModuleDataUseCase,
        private UpdateModuleUseCase $updateModuleUseCase
    ) {}

    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $courseId = (int) $request->route('courseId');

        $moduleData = $this->getModuleDataUseCase->execute($userId, $courseId);

        return response()->json($moduleData);
    }

    public function update(
        int $moduleId,
        UpdateModuleRequest $request
    ): JsonResponse {
        $validated = $request->validated();

        $module = $this->updateModuleUseCase->execute(
            userId: (int) $request->user()->id,
            moduleId: $moduleId,
            name: $validated['name']
        );

        return response()->json([
            'data' => $module,
            'message' => 'Módulo actualizado correctamente.',
        ]);
    }
}
