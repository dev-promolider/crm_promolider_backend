<?php

namespace Promolider\Infrastructure\Marketing\In\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Promolider\Application\Marketing\UseCases\FreeCourses\ListFreeCoursesUseCase;
use Promolider\Application\Marketing\UseCases\FreeCourses\CreateFreeCourseUseCase;
use Promolider\Application\Marketing\UseCases\FreeCourses\DeleteFreeCourseUseCase;

class FreeCoursesController extends Controller
{
    public function __construct(
        private ListFreeCoursesUseCase $listUseCase,
        private CreateFreeCourseUseCase $createUseCase,
        private DeleteFreeCourseUseCase $deleteUseCase,
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['search', 'status']);
            $courses = $this->listUseCase->execute($filters);
            return response()->json(['success' => true, 'data' => $courses]);
        } catch (\Exception $e) {
            Log::error('Error listing free courses: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener cursos gratuitos'], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'course_name' => 'required|string|max:255',
                'category_id' => 'nullable|integer|exists:categories,id',
                'description' => 'nullable|string|max:500',
                'status' => 'nullable|string|in:active,inactive',
            ]);

            $data['status'] = $data['status'] ?? 'active';

            $course = $this->createUseCase->execute($data);
            return response()->json(['success' => true, 'data' => $course], 201);
        } catch (\Exception $e) {
            Log::error('Error creating free course: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear curso gratuito: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->deleteUseCase->execute($id);
            if (!$result) {
                return response()->json(['success' => false, 'message' => 'Curso no encontrado'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Curso gratuito eliminado']);
        } catch (\Exception $e) {
            Log::error('Error deleting free course: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar curso gratuito'], 500);
        }
    }
}
