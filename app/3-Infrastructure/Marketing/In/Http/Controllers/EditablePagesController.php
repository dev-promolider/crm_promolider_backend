<?php

namespace Promolider\Infrastructure\Marketing\In\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Promolider\Application\Marketing\UseCases\Pages\ListEditablePagesUseCase;
use Promolider\Application\Marketing\UseCases\Pages\CreateEditablePageUseCase;
use Promolider\Application\Marketing\UseCases\Pages\UpdateEditablePageUseCase;
use Promolider\Application\Marketing\UseCases\Pages\DeleteEditablePageUseCase;
use Promolider\Application\Marketing\UseCases\Pages\GetPublicEditablePageUseCase;

class EditablePagesController extends Controller
{
    public function __construct(
        private readonly ListEditablePagesUseCase $listEditablePagesUseCase,
        private readonly CreateEditablePageUseCase $createEditablePageUseCase,
        private readonly UpdateEditablePageUseCase $updateEditablePageUseCase,
        private readonly DeleteEditablePageUseCase $deleteEditablePageUseCase,
        private readonly GetPublicEditablePageUseCase $getPublicEditablePageUseCase,
    ) {}

    public function index(): JsonResponse
    {
        try {
            $pages = $this->listEditablePagesUseCase->execute();
            return response()->json(['success' => true, 'data' => $pages]);
        } catch (\Exception $e) {
            Log::error('Error listing editable pages: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al listar plantillas editables'], 500);
        }
    }

    public function userPages(int $userId): JsonResponse
    {
        try {
            $pages = $this->listEditablePagesUseCase->getByUser($userId);
            return response()->json(['success' => true, 'data' => $pages]);
        } catch (\Exception $e) {
            Log::error('Error getting user editable pages: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener plantillas del usuario'], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $page = $this->listEditablePagesUseCase->getById($id);
            if (!$page) {
                return response()->json(['success' => false, 'message' => 'Plantilla editable no encontrada'], 404);
            }
            return response()->json(['success' => true, 'data' => [
                'id' => $page->id,
                'user_id' => $page->userId,
                'template_id' => $page->templateId,
                'title' => $page->title,
                'content_html' => $page->contentHtml,
                'edited_fields' => $page->editedFields,
                'status' => $page->status,
                'slug' => $page->slug,
                'public_url' => $page->publicUrl,
            ]]);
        } catch (\Exception $e) {
            Log::error('Error getting editable page: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener plantilla editable'], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'template_id' => 'required|integer|exists:template,id',
                'title' => 'required|string|max:255',
                'content_html' => 'required|string',
                'edited_fields' => 'nullable|string',
                'status' => 'required|in:draft,published',
                'slug' => 'nullable|string|max:255|unique:edit_template,slug',
            ]);

            // Use the authenticated user's ID for security
            $validated['user_id'] = $request->user()->id;

            $result = $this->createEditablePageUseCase->execute($validated);
            return response()->json(['success' => true, 'data' => $result], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Datos inválidos', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error creating editable page: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear plantilla editable'], 500);
        }
    }

    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'title' => 'nullable|string|max:255',
                'content_html' => 'nullable|string',
                'edited_fields' => 'nullable|string',
                'status' => 'nullable|in:draft,published',
                'slug' => 'nullable|string|max:255|unique:edit_template,slug,' . $id,
            ]);

            $result = $this->updateEditablePageUseCase->execute($id, $validated);
            if (!$result) {
                return response()->json(['success' => false, 'message' => 'Plantilla editable no encontrada'], 404);
            }
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['success' => false, 'message' => 'Datos inválidos', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error updating editable page: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar plantilla editable'], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->deleteEditablePageUseCase->execute($id);
            if (!$deleted) {
                return response()->json(['success' => false, 'message' => 'Plantilla editable no encontrada'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Plantilla editable eliminada']);
        } catch (\Exception $e) {
            Log::error('Error deleting editable page: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar plantilla editable'], 500);
        }
    }

    public function publicPage(string $slug): \Illuminate\Http\Response|JsonResponse
    {
        try {
            $page = $this->getPublicEditablePageUseCase->execute($slug);
            if (!$page) {
                return response()->json(['success' => false, 'message' => 'Página no encontrada'], 404);
            }

            return response($page->contentHtml)
                ->header('Content-Type', 'text/html');
        } catch (\Exception $e) {
            Log::error('Error serving public editable page: ' . $e->getMessage());
            abort(500, 'Error interno del servidor');
        }
    }
}
