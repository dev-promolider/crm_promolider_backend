<?php

namespace Promolider\Infrastructure\Marketing\In\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Promolider\Application\Marketing\UseCases\Tools\GetToolsUseCase;
use Promolider\Application\Marketing\UseCases\Tools\GetToolUseCase;
use Promolider\Application\Marketing\UseCases\Tools\GetCampaignsUseCase;
use Promolider\Application\Marketing\UseCases\Tools\GetCategoriesUseCase;
use Promolider\Application\Marketing\UseCases\Tools\UpdateToolStatusUseCase;
use Promolider\Application\Marketing\UseCases\Tools\UpdateToolUseCase;
use Promolider\Application\Marketing\UseCases\Tools\DeleteToolUseCase;
use Promolider\Application\Marketing\UseCases\Tools\StoreToolUseCase;
use Promolider\Domain\Marketing\Ports\Out\ToolRepositoryInterface;

class MarketingToolsController extends Controller
{
    public function __construct(
        private GetToolsUseCase $getToolsUseCase,
        private GetToolUseCase $getToolUseCase,
        private GetCampaignsUseCase $getCampaignsUseCase,
        private GetCategoriesUseCase $getCategoriesUseCase,
        private UpdateToolStatusUseCase $updateToolStatusUseCase,
        private UpdateToolUseCase $updateToolUseCase,
        private DeleteToolUseCase $deleteToolUseCase,
        private StoreToolUseCase $storeToolUseCase,
        private ToolRepositoryInterface $toolRepository,
    ) {}

    public function tools(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $tools = $this->getToolsUseCase->execute($userId);
            return response()->json(['success' => true, 'data' => $tools]);
        } catch (\Exception $e) {
            Log::error('Error getting tools: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener herramientas'], 500);
        }
    }

    /**
     * GET /api/v1/marketing/{type}/{id}
     * Obtiene una herramienta por ID para editar.
     */
    public function getTool(string $type, int $id): JsonResponse
    {
        try {
            if (!in_array($type, ['masterclass', 'ebook', 'mini-course', 'minicourse'])) {
                return response()->json(['success' => false, 'message' => 'Tipo inválido'], 400);
            }
            $tool = $this->getToolUseCase->execute($type, $id);
            if (!$tool) {
                return response()->json(['success' => false, 'message' => 'Herramienta no encontrada'], 404);
            }
            return response()->json(['success' => true, 'data' => $tool]);
        } catch (\Exception $e) {
            Log::error("Error getting {$type} {$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener herramienta'], 500);
        }
    }

    /**
     * PUT /api/v1/marketing/{type}/{id}
     * Actualiza una herramienta (masterclass, ebook o minicourse).
     */
    public function updateTool(Request $request, string $type, int $id): JsonResponse
    {
        try {
            if (!in_array($type, ['masterclass', 'ebook', 'mini-course', 'minicourse'])) {
                return response()->json(['success' => false, 'message' => 'Tipo inválido'], 400);
            }

            $userId = $request->user()->id;

            // Verificar ownership
            if (!$this->toolRepository->verifyToolOwnership($type, $id, $userId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para modificar esta herramienta'
                ], 403);
            }

            $data = $request->except(['_method', '_token']);
            $result = $this->updateToolUseCase->execute($type, $id, $data);

            if (!$result) {
                return response()->json(['success' => false, 'message' => 'No se pudo actualizar'], 400);
            }

            return response()->json(['success' => true, 'message' => 'Herramienta actualizada correctamente']);
        } catch (\Exception $e) {
            Log::error("Error updating {$type} {$id}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar herramienta'], 500);
        }
    }

    /**
     * GET /api/v1/marketing/campaigns
     * Retorna TODAS las campañas activas (status=2) de todos los productores.
     */
    public function getCampaigns(): JsonResponse
    {
        try {
            $campaigns = $this->getCampaignsUseCase->execute();
            return response()->json(['success' => true, 'data' => $campaigns]);
        } catch (\Exception $e) {
            Log::error('Error getting campaigns: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener campañas'], 500);
        }
    }

    /**
     * GET /api/v1/marketing/campaigns/mine
     * Retorna SOLO las campañas del usuario autenticado (status=2).
     */
    public function getUserCampaigns(Request $request): JsonResponse
    {
        try {
            $userId = $request->user()->id;
            $campaigns = $this->toolRepository->getUserCampaigns($userId);
            return response()->json(['success' => true, 'data' => $campaigns]);
        } catch (\Exception $e) {
            Log::error('Error getting user campaigns: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener tus campañas'], 500);
        }
    }

    public function getCampaignsByType(string $type): JsonResponse
    {
        try {
            $campaigns = $this->getCampaignsUseCase->getByType($type);
            return response()->json(['success' => true, 'data' => $campaigns]);
        } catch (\Exception $e) {
            Log::error('Error getting campaigns by type: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener campañas'], 500);
        }
    }

    public function getCategories(Request $request): JsonResponse
    {
        try {
            $type = $request->get('type');
            $categories = $this->getCategoriesUseCase->execute($type);
            return response()->json(['success' => true, 'data' => $categories]);
        } catch (\Exception $e) {
            Log::error('Error getting categories: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener categorías'], 500);
        }
    }

    public function createCategory(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'type' => 'nullable|string',
                'icon' => 'nullable|string',
                'parent_id' => 'nullable|integer',
            ]);
            $category = $this->getCategoriesUseCase->create($data);
            return response()->json(['success' => true, 'data' => $category], 201);
        } catch (\Exception $e) {
            Log::error('Error creating category: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear categoría'], 500);
        }
    }

    /**
     * PATCH /api/v1/marketing/{type}/{toolId}/status
     * Cambia el estado de una herramienta (0=inactivo, 1=publicado, 2=campaña activa).
     */
    public function updateStatus(Request $request, string $type, int $toolId): JsonResponse
    {
        try {
            $request->validate([
                'status' => 'required|in:0,1,2',
            ]);

            $userId = $request->user()->id;

            // Verificar que el producto pertenece al usuario
            if (!$this->toolRepository->verifyToolOwnership($type, $toolId, $userId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para modificar esta herramienta'
                ], 403);
            }

            $result = $this->updateToolStatusUseCase->execute($type, $toolId, $request->status);

            $statusLabels = ['0' => 'No publicado', '1' => 'Publicado', '2' => 'Campaña activa'];

            return response()->json([
                'success' => $result,
                'message' => "Estado actualizado a: {$statusLabels[$request->status]}",
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Estado inválido. Valores permitidos: 0 (inactivo), 1 (publicado), 2 (campaña activa)',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error("Error updating {$type} status: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar estado'], 500);
        }
    }

    public function delete(string $type, int $toolId): JsonResponse
    {
        try {
            $result = $this->deleteToolUseCase->execute($type, $toolId);
            return response()->json(['success' => $result]);
        } catch (\Exception $e) {
            Log::error("Error deleting {$type}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar'], 500);
        }
    }

    public function store(Request $request, string $type): JsonResponse
    {
        try {
            $userId = $request->user()->id;

            $toolId = match ($type) {
                'masterclass' => $this->storeMasterclass($request, $userId),
                'ebook' => $this->storeEbook($request, $userId),
                'mini-course', 'minicourse' => $this->storeMiniCourse($request, $userId),
                default => throw new \InvalidArgumentException("Invalid type: {$type}"),
            };

            return response()->json(['success' => true, 'data' => ['id' => $toolId]], 201);
        } catch (\Exception $e) {
            Log::error("Error storing {$type}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al guardar'], 500);
        }
    }

    private function storeMasterclass(Request $request, int $userId): int
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category_id' => 'required|integer',
            'description' => 'required|string',
            'objective' => 'required|string',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'duration' => 'required|integer|min:1',
            'meeting_link' => 'required|url',
            'email' => 'required|email',
            'phone' => 'required|string',
            'image' => 'nullable|file|mimes:jpeg,jpg,png,webp|max:2048',
            'documents' => 'nullable|array',
            'documents.*' => 'nullable|file|mimes:doc,docx,pdf,xls,xlsx,txt|max:5120',
        ]);

        $data = [
            'user_id' => $userId,
            'title' => $validated['title'],
            'id_categories' => $validated['category_id'],
            'description' => $validated['description'],
            'objectives' => $validated['objective'],
            'date' => $validated['date'],
            'hour' => $validated['time'],
            'duration' => $validated['duration'],
            'meeting_link' => $validated['meeting_link'],
            'email_contact' => $validated['email'],
            'phone_contact' => $validated['phone'],
            'status' => 1,
        ];

        $toolId = $this->storeToolUseCase->execute('masterclass', $data);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('masterclass_images', 's3');
            \App\Models\MasterclassImage::create([
                'masterclass_id' => $toolId,
                'image' => $imagePath,
            ]);
        }

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $doc) {
                $docPath = $doc->store('masterclass_documents', 's3');
                \App\Models\MasterclassDocument::create([
                    'masterclass_id' => $toolId,
                    'document' => $docPath,
                ]);
            }
        }

        return $toolId;
    }

    private function storeEbook(Request $request, int $userId): int
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'pages' => 'required|integer|min:1',
            'category_id' => 'required|integer',
            'description' => 'required|string',
            'cover' => 'nullable|file|mimes:jpeg,jpg,png,webp|max:2048',
            'pdf' => 'nullable|file|mimes:pdf|max:10240',
            'chapters' => 'nullable|array',
            'chapters.*.title' => 'required_with:chapters|string|max:255',
            'chapters.*.content' => 'required_with:chapters|string',
            'chapters.*.pages' => 'required_with:chapters|integer|min:1',
        ]);

        $data = [
            'user_id' => $userId,
            'title' => $validated['title'],
            'author' => $validated['author'],
            'pages' => $validated['pages'],
            'category_id' => $validated['category_id'],
            'description' => $validated['description'],
            'status' => 1,
        ];

        $toolId = $this->storeToolUseCase->execute('ebook', $data);

        if ($request->hasFile('cover')) {
            $imagePath = $request->file('cover')->store("ebooks/{$userId}/{$toolId}/images", 's3');
            \App\Models\EbookImage::create([
                'ebook_id' => $toolId,
                'image' => $imagePath,
            ]);
        }

        if ($request->hasFile('pdf')) {
            $docPath = $request->file('pdf')->store("ebooks/{$userId}/{$toolId}/documents", 's3');
            \App\Models\EbookDocument::create([
                'ebook_id' => $toolId,
                'document' => $docPath,
            ]);
        }

        if ($request->has('chapters') && is_array($request->input('chapters'))) {
            foreach ($request->input('chapters') as $chapter) {
                \App\Models\EbookChapter::create([
                    'ebook_id' => $toolId,
                    'title' => $chapter['title'],
                    'content' => $chapter['content'],
                    'pages' => $chapter['pages'],
                ]);
            }
        }

        return $toolId;
    }

    private function storeMiniCourse(Request $request, int $userId): int
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'duration' => 'required|integer|min:1',
            'level' => 'required|string|in:principiante,intermedio,avanzado,Principiante,Intermedio,Avanzado',
            'category_id' => 'required|integer',
            'image' => 'nullable|file|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        $data = [
            'user_id' => $userId,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'duration' => $validated['duration'],
            'level' => $validated['level'],
            'category_id' => $validated['category_id'],
            'status' => 1,
        ];

        $toolId = $this->storeToolUseCase->execute('minicourse', $data);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store("mini_courses/{$userId}/{$toolId}/images", 's3');
            \App\Models\MiniCourseImage::create([
                'mini_course_id' => $toolId,
                'image' => $imagePath,
            ]);
        }

        return $toolId;
    }
}
