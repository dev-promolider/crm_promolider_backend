<?php

namespace Promolider\Infrastructure\Marketing\In\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Promolider\Application\Marketing\UseCases\Dinamicas\ManageDinamicasUseCase;

class DinamicasController extends Controller
{
    public function __construct(
        private ManageDinamicasUseCase $manageDinamicasUseCase
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $courseId = $request->query('course_id');
            $dinamicas = $this->manageDinamicasUseCase->getAll($userId, $courseId);
            return response()->json(['success' => true, 'data' => $dinamicas]);
        } catch (\Exception $e) {
            Log::error('Error getting dinamicas: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener dinámicas'], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $userId = Auth::id();
            $result = $this->manageDinamicasUseCase->getById($id, $userId);

            if (!$result) {
                return response()->json(['success' => false, 'message' => 'Dinámica no encontrada'], 404);
            }

            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('Error getting dinamica: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener dinámica'], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'nombre' => 'required|string|max:255',
                'tipo_dinamica' => 'required|string|in:ruleta,trivia',
                'descripcion' => 'nullable|string',
                'category_id' => 'nullable|integer',
                'is_public' => 'nullable|boolean',
                'course_id' => 'required|integer|exists:courses,id',
            ]);

            $userId = Auth::id();
            $result = $this->manageDinamicasUseCase->store($data, $userId);

            if (!($result['success'] ?? false)) {
                return response()->json($result, 400);
            }

            return response()->json($result, 201);
        } catch (\Exception $e) {
            Log::error('Error creating dinamica: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear dinámica'], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $data = $request->validate([
                'nombre' => 'nullable|string|max:255',
                'descripcion' => 'nullable|string',
                'category_id' => 'nullable|integer',
                'is_public' => 'nullable|boolean',
                'is_active' => 'nullable|boolean',
            ]);

            $userId = Auth::id();
            $result = $this->manageDinamicasUseCase->update($id, $data, $userId);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error updating dinamica: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar dinámica'], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $userId = Auth::id();
            $result = $this->manageDinamicasUseCase->delete($id, $userId);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error deleting dinamica: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar dinámica'], 500);
        }
    }

    public function toggleStatus(int $id): JsonResponse
    {
        try {
            $userId = Auth::id();
            $result = $this->manageDinamicasUseCase->toggleStatus($id, $userId);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error toggling dinamica status: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al cambiar estado'], 500);
        }
    }

    public function storeSpecifications(Request $request, int $dinamicaId): JsonResponse
    {
        try {
            $data = $request->validate([
                'nombre' => 'nullable|string|max:255',
                'descripcion' => 'nullable|string',
                'category_id' => 'nullable|integer',
                'modoInscripcion' => 'nullable|string',
                'tiempoInscripcion' => 'nullable|integer',
                'maxParticipantes' => 'nullable|integer',
                'mostrarInscritos' => 'nullable|boolean',
                'tipoPremio' => 'nullable|string',
                'maxGanadores' => 'nullable|integer',
                'premios' => 'nullable|array',
                'premios.*.nombre' => 'required_with:premios|string',
                'premios.*.tipo' => 'required_with:premios|string',
                'premios.*.stock' => 'nullable|integer',
                'premios.*.peso' => 'nullable|integer',
                'premios.*.limiteUsuario' => 'nullable|integer',
                'premios.*.vigenciaInicio' => 'nullable|date',
                'premios.*.vigenciaFin' => 'nullable|date',
                'premios.*.claimUrl' => 'nullable|string',
            ]);

            $userId = Auth::id();
            $result = $this->manageDinamicasUseCase->storeSpecifications($dinamicaId, $data, $userId);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error storing dinamica specifications: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al guardar especificaciones'], 500);
        }
    }

    public function saveTrivia(Request $request, int $dinamicaId): JsonResponse
    {
        try {
            $data = $request->validate([
                'nombre' => 'nullable|string|max:255',
                'descripcion' => 'nullable|string',
                // Nuevos campos (DinamicaTriviaConfig)
                'registration_config' => 'nullable|array',
                'registrationConfig' => 'nullable|array',
                'trivia_config' => 'nullable|array',
                'triviaConfig' => 'nullable|array',
                'game_blocks' => 'nullable|array',
                'gameBlocks' => 'nullable|array',
                // Legacy fields (mapped to new ones)
                'questions' => 'nullable|array',
                'config' => 'nullable|array',
            ]);

            $userId = Auth::id();
            $result = $this->manageDinamicasUseCase->saveTrivia($dinamicaId, $data, $userId);

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error saving trivia: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al guardar trivia'], 500);
        }
    }
}
