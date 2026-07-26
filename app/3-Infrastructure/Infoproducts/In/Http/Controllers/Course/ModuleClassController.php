<?php

namespace Promolider\Infrastructure\Infoproducts\In\Http\Controllers\Course;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Http\JsonResponse;
use Promolider\Infrastructure\Infoproducts\In\Http\Requests\Course\UpdateClassRequest;
use Promolider\Infrastructure\Infoproducts\In\Http\Requests\Course\StoreModuleClassRequest;
use Promolider\Application\Infoproducts\UseCases\Course\Class\GetModuleClassDataUseCase;
use Promolider\Application\Infoproducts\UseCases\Course\Class\SaveModuleClassUseCase;
use Promolider\Application\Infoproducts\UseCases\Course\GenerateClassVideoUploadUrlUseCase;
use Promolider\Application\Infoproducts\UseCases\Course\UpdateModuleClassUseCase;

class ModuleClassController extends Controller
{
    public function __construct(
        private GetModuleClassDataUseCase $getModuleClassDataUseCase,
        private GenerateClassVideoUploadUrlUseCase $generateVideoUploadUrlUseCase,
        private SaveModuleClassUseCase $saveModuleClassUseCase,
        private UpdateModuleClassUseCase $updateModuleClassUseCase
    ) {}

    public function save(StoreModuleClassRequest $request) : JsonResponse
    {
        $validated = $request->validated();

        $resources = $request->file('resources', []);

        $class = $this->saveModuleClassUseCase->execute(
            userId: (int) $request->user()->id,
            moduleId: (int) $validated['module_id'],
            data: [
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
            ],
            resources: $resources,
        );

        return response()->json([
            'data' => $class,
            'message' => 'Clase creada correctamente.',
        ], 201);
    }

    public function update(
        int $classId,
        UpdateClassRequest $request
    ): JsonResponse {
        $validated = $request->validated();

        $result = $this->updateModuleClassUseCase->execute(
            userId: (int) $request->user()->id,
            classId: $classId,
            data: [
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'time' => $validated['time'] ?? null,
            ],
            resources: $request->file('resources', []),
            resourcesRemoved: $validated['resourcesRemoved'] ?? []
        );

        return response()->json([
            'data' => $result,
            'message' => 'Registro actualizado.',
        ]);
    }

    public function generateVideoUploadUrl(
        Request $request,
        int $id
    ): JsonResponse {
        $validated = $request->validate([
            'filename' => [
                'required',
                'string',
                'max:255',
                'regex:/\.(mp4|mov|avi|mkv|webm)$/i',
            ],
        ], [
            'filename.required' => 'El nombre del archivo es obligatorio.',
            'filename.regex' => 'El archivo debe ser un video válido.',
        ]);

        $result = $this->generateVideoUploadUrlUseCase->execute(
            userId: (int) $request->user()->id,
            classId: $id,
            filename: $validated['filename']
        );

        return response()->json([
            'data' => $result,
            'message' => 'URL generada correctamente.',
        ]);
    }

    public function getClassList(Request $request)
    {
        $moduleId = (int) $request->route('moduleId');

        $lessonData = $this->getModuleClassDataUseCase->execute($moduleId);

        return response()->json($lessonData, 200);
    }
}
