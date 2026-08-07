<?php

namespace Promolider\Infrastructure\Infoproducts\In\Http\Controllers\Course;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Promolider\Application\Infoproducts\UseCases\Course\Module\GetModuleDataUseCase;
use Promolider\Application\Infoproducts\UseCases\Course\Module\CreateModuleUseCase;
use Illuminate\Support\Facades\Log;

class ModuleController extends Controller
{
    public function __construct(
        private GetModuleDataUseCase $getModuleDataUseCase,
        private CreateModuleUseCase $createModuleUseCase
    ) {}

    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $courseId = (int) $request->route('courseId');

        $moduleData = $this->getModuleDataUseCase->execute($userId, $courseId);

        return response()->json($moduleData);
    }

    public function store(Request $request)
    {
        try {
            $courseId = (int) $request->course_id;
            $name = $request->name;

            $result = $this->createModuleUseCase->execute($courseId, $name);

            return response()->json([
                'data' => $result,
                'message' => 'Registro exitoso'
            ], 200);

        } catch (\Throwable $th) {
            Log::error('Error creating module: ' . $th->getMessage());
            return response()->json([
                'data' => ['status' => 'error'],
                'message' => $th->getMessage()
            ], 422);
        }
    }

    public function update(Request $request, $id, \Promolider\Application\Infoproducts\UseCases\Course\Module\UpdateModuleUseCase $updateModuleUseCase)
    {
        try {
            $name = $request->name;
            $result = $updateModuleUseCase->execute((int)$id, $name);
            return response()->json([
                'data' => $result,
                'message' => 'Módulo actualizado exitosamente'
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Error updating module: ' . $th->getMessage());
            return response()->json(['message' => 'Error al actualizar'], 422);
        }
    }

    public function destroy($id, \Promolider\Application\Infoproducts\UseCases\Course\Module\DeleteModuleUseCase $deleteModuleUseCase)
    {
        try {
            $result = $deleteModuleUseCase->execute((int)$id);
            return response()->json([
                'data' => $result,
                'message' => 'Módulo eliminado exitosamente'
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Error deleting module: ' . $th->getMessage());
            return response()->json(['message' => 'Error al eliminar'], 422);
        }
    }

    public function reorder(Request $request, \Promolider\Application\Infoproducts\UseCases\Course\Module\ReorderModulesUseCase $reorderModulesUseCase)
    {
        try {
            $orderedIds = $request->ordered_ids ?? [];
            $result = $reorderModulesUseCase->execute($orderedIds);
            return response()->json([
                'data' => $result,
                'message' => 'Módulos reordenados exitosamente'
            ], 200);
        } catch (\Throwable $th) {
            Log::error('Error reordering modules: ' . $th->getMessage());
            return response()->json(['message' => 'Error al reordenar'], 422);
        }
    }
}
