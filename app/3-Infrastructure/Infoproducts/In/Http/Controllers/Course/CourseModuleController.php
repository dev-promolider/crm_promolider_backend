<?php

namespace Promolider\Infrastructure\Infoproducts\In\Http\Controllers\Course;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Promolider\Application\Infoproducts\UseCases\Course\Module\GetModuleDataUseCase;
use Promolider\Application\Infoproducts\UseCases\Course\StoreModuleUseCase;
use Promolider\Application\Infoproducts\UseCases\Course\UpdateModuleUseCase;
use Promolider\Infrastructure\Infoproducts\In\Http\Requests\Course\ModuleRequest;
use Promolider\Infrastructure\Infoproducts\In\Http\Requests\Course\UpdateModuleRequest;

class CourseModuleController extends Controller
{
    public function __construct(
        private GetModuleDataUseCase $getModuleDataUseCase,
        private UpdateModuleUseCase $updateModuleUseCase,
        private StoreModuleUseCase $storeModuleUseCase
    ) {}

    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $courseId = (int) $request->route('courseId');

        $moduleData = $this->getModuleDataUseCase->execute($userId, $courseId);

        return response()->json($moduleData);
    }

    public function store(ModuleRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->storeModuleUseCase->execute(
            userId: (int) $request->user()->id,
            courseId: (int) $validated['course_id'],
            name: $validated['name']
        );

        return response()->json([
            'data' => $result,
            'message' => 'Módulo creado correctamente.',
        ], 201);
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
