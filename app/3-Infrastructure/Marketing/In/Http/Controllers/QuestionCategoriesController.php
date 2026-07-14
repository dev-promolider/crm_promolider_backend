<?php

namespace Promolider\Infrastructure\Marketing\In\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Promolider\Application\Marketing\UseCases\QuestionCategories\ListQuestionCategoriesUseCase;
use Promolider\Application\Marketing\UseCases\QuestionCategories\GetQuestionCategoryUseCase;
use Promolider\Application\Marketing\UseCases\QuestionCategories\CreateQuestionCategoryUseCase;
use Promolider\Application\Marketing\UseCases\QuestionCategories\UpdateQuestionCategoryUseCase;
use Promolider\Application\Marketing\UseCases\QuestionCategories\ToggleQuestionCategoryStatusUseCase;
use Promolider\Application\Marketing\UseCases\QuestionCategories\DeleteQuestionCategoryUseCase;
use Promolider\Application\Marketing\UseCases\QuestionItems\ListQuestionItemsUseCase;
use Promolider\Application\Marketing\UseCases\QuestionItems\CreateQuestionItemUseCase;
use Promolider\Application\Marketing\UseCases\QuestionItems\UpdateQuestionItemUseCase;
use Promolider\Application\Marketing\UseCases\QuestionItems\DeleteQuestionItemUseCase;

class QuestionCategoriesController extends Controller
{
    public function __construct(
        private ListQuestionCategoriesUseCase $listCategoriesUseCase,
        private GetQuestionCategoryUseCase $getCategoryUseCase,
        private CreateQuestionCategoryUseCase $createCategoryUseCase,
        private UpdateQuestionCategoryUseCase $updateCategoryUseCase,
        private ToggleQuestionCategoryStatusUseCase $toggleCategoryUseCase,
        private DeleteQuestionCategoryUseCase $deleteCategoryUseCase,
        private ListQuestionItemsUseCase $listItemsUseCase,
        private CreateQuestionItemUseCase $createItemUseCase,
        private UpdateQuestionItemUseCase $updateItemUseCase,
        private DeleteQuestionItemUseCase $deleteItemUseCase,
    ) {}

    // ─── Question Categories ───────────────────────────────────────────────

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['search', 'is_active']);
            $categories = $this->listCategoriesUseCase->execute($filters);
            return response()->json(['success' => true, 'data' => $categories]);
        } catch (\Exception $e) {
            Log::error('Error listing question categories: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener categorías'], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $category = $this->getCategoryUseCase->execute($id);
            if (!$category) {
                return response()->json(['success' => false, 'message' => 'Categoría no encontrada'], 404);
            }
            return response()->json(['success' => true, 'data' => $category]);
        } catch (\Exception $e) {
            Log::error('Error showing question category: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener categoría'], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:120',
                'description' => 'nullable|string|max:255',
                'is_active' => 'nullable|boolean',
            ]);

            $data['is_active'] = $data['is_active'] ?? true;
            $data['created_by'] = $request->user()->id ?? null;
            $data['updated_by'] = $request->user()->id ?? null;

            $category = $this->createCategoryUseCase->execute($data);
            return response()->json(['success' => true, 'data' => $category], 201);
        } catch (\Exception $e) {
            Log::error('Error creating question category: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear categoría'], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:120',
                'description' => 'nullable|string|max:255',
                'is_active' => 'nullable|boolean',
            ]);

            $data['updated_by'] = $request->user()->id ?? null;

            $category = $this->updateCategoryUseCase->execute($id, $data);
            if (!$category) {
                return response()->json(['success' => false, 'message' => 'Categoría no encontrada'], 404);
            }
            return response()->json(['success' => true, 'data' => $category]);
        } catch (\Exception $e) {
            Log::error('Error updating question category: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar categoría'], 500);
        }
    }

    public function toggle(int $id): JsonResponse
    {
        try {
            $category = $this->toggleCategoryUseCase->execute($id);
            if (!$category) {
                return response()->json(['success' => false, 'message' => 'Categoría no encontrada'], 404);
            }
            return response()->json(['success' => true, 'data' => $category]);
        } catch (\Exception $e) {
            Log::error('Error toggling question category: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al cambiar estado'], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $result = $this->deleteCategoryUseCase->execute($id);
            if (!$result) {
                return response()->json(['success' => false, 'message' => 'Categoría no encontrada'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Categoría eliminada']);
        } catch (\Exception $e) {
            Log::error('Error deleting question category: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar categoría'], 500);
        }
    }

    // ─── Question Items ────────────────────────────────────────────────────

    public function questionsIndex(Request $request, int $categoryId): JsonResponse
    {
        try {
            $filters = $request->only(['difficulty', 'status', 'search']);
            $questions = $this->listItemsUseCase->execute($categoryId, $filters);
            return response()->json(['success' => true, 'data' => $questions]);
        } catch (\Exception $e) {
            Log::error('Error listing questions: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener preguntas'], 500);
        }
    }

    public function storeQuestion(Request $request, int $categoryId): JsonResponse
    {
        try {
            $data = $request->validate([
                'title' => 'required|string|max:255',
                'body' => 'nullable|string|max:1000',
                'status' => 'required|string|in:draft,published,archived',
                'difficulty' => 'required|string|in:easy,medium,hard',
                'time_limit' => 'nullable|integer|min:5|max:600',
                'is_active' => 'nullable|boolean',
                'options' => 'required|array|min:2|max:6',
                'options.*.text' => 'required|string|max:255',
                'options.*.is_correct' => 'nullable|boolean',
            ]);

            $question = $this->createItemUseCase->execute(
                $categoryId,
                $data,
                $request->user()->id
            );

            return response()->json(['success' => true, 'data' => $question], 201);
        } catch (\Exception $e) {
            Log::error('Error creating question: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear pregunta: ' . $e->getMessage()], 500);
        }
    }

    public function updateQuestion(Request $request, int $categoryId, int $questionId): JsonResponse
    {
        try {
            $data = $request->validate([
                'title' => 'nullable|string|max:255',
                'body' => 'nullable|string|max:1000',
                'status' => 'nullable|string|in:draft,published,archived',
                'difficulty' => 'nullable|string|in:easy,medium,hard',
                'time_limit' => 'nullable|integer|min:5|max:600',
                'is_active' => 'nullable|boolean',
                'options' => 'nullable|array|min:2|max:6',
                'options.*.text' => 'required_with:options|string|max:255',
                'options.*.is_correct' => 'nullable|boolean',
            ]);

            $question = $this->updateItemUseCase->execute(
                $questionId,
                $data,
                $request->user()->id
            );

            if (!$question) {
                return response()->json(['success' => false, 'message' => 'Pregunta no encontrada'], 404);
            }

            return response()->json(['success' => true, 'data' => $question]);
        } catch (\Exception $e) {
            Log::error('Error updating question: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar pregunta'], 500);
        }
    }

    public function destroyQuestion(int $categoryId, int $questionId): JsonResponse
    {
        try {
            $result = $this->deleteItemUseCase->execute($questionId);
            if (!$result) {
                return response()->json(['success' => false, 'message' => 'Pregunta no encontrada'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Pregunta eliminada']);
        } catch (\Exception $e) {
            Log::error('Error deleting question: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar pregunta'], 500);
        }
    }
}
