<?php

namespace Promolider\Infrastructure\Marketing\In\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Promolider\Application\Marketing\UseCases\Pages\GetTemplatesUseCase;
use Promolider\Application\Marketing\UseCases\Pages\CreatePageUseCase;
use Promolider\Application\Marketing\UseCases\Pages\UpdatePageUseCase;
use Promolider\Application\Marketing\UseCases\Pages\DeletePageUseCase;
use Promolider\Application\Marketing\UseCases\Pages\PublishPageUseCase;
use Promolider\Application\Marketing\UseCases\Pages\GetPublicPageUseCase;

class PagesController extends Controller
{
    public function __construct(
        private GetTemplatesUseCase $getTemplatesUseCase,
        private CreatePageUseCase $createPageUseCase,
        private UpdatePageUseCase $updatePageUseCase,
        private DeletePageUseCase $deletePageUseCase,
        private PublishPageUseCase $publishPageUseCase,
        private GetPublicPageUseCase $getPublicPageUseCase,
    ) {}

    /**
     * Obtener plantillas base (públicas, sin user_id)
     */
    public function getTemplates(): \Illuminate\Http\JsonResponse
    {
        try {
            $templates = $this->getTemplatesUseCase->execute();
            return response()->json(['success' => true, 'data' => $templates]);
        } catch (\Exception $e) {
            Log::error('Error getting templates: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener plantillas'], 500);
        }
    }

    /**
     * Obtener páginas de un usuario
     */
    public function getUserTemplates(Request $request, int $userId): \Illuminate\Http\JsonResponse
    {
        try {
            $pages = $this->getTemplatesUseCase->getUserPages($userId);
            return response()->json(['success' => true, 'data' => $pages]);
        } catch (\Exception $e) {
            Log::error('Error getting user pages: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener páginas del usuario'], 500);
        }
    }

    /**
     * Obtener una página por ID
     */
    public function getTemplate(int $id): \Illuminate\Http\JsonResponse
    {
        try {
            $page = $this->getTemplatesUseCase->getPage($id);
            if (!$page) {
                return response()->json(['success' => false, 'message' => 'Plantilla no encontrada'], 404);
            }
            return response()->json(['success' => true, 'data' => $page]);
        } catch (\Exception $e) {
            Log::error('Error getting template: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener plantilla'], 500);
        }
    }

    /**
     * Crear una nueva página
     */
    public function create(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $request->validate([
                'user_id' => 'required|integer',
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|max:255',
                'content' => 'nullable|string',
                'content_html' => 'nullable|string',
                'styles_css' => 'nullable|string',
                'thumbnail' => 'nullable|string',
                'description' => 'nullable|string',
                'template' => 'nullable|string',
                'edited_fields' => 'nullable|string',
                'status' => 'nullable|string|in:draft,published,active',
                'type' => 'nullable|string',
                'meta' => 'nullable|array',
            ]);
            $page = $this->createPageUseCase->execute($data);
            return response()->json(['success' => true, 'data' => $page], 201);
        } catch (\Exception $e) {
            Log::error('Error creating page: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear página: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Actualizar una página
     */
    public function update(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $request->validate([
                'title' => 'nullable|string|max:255',
                'slug' => 'nullable|string|max:255',
                'content' => 'nullable|string',
                'content_html' => 'nullable|string',
                'styles_css' => 'nullable|string',
                'thumbnail' => 'nullable|string',
                'description' => 'nullable|string',
                'template' => 'nullable|string',
                'edited_fields' => 'nullable|string',
                'status' => 'nullable|string|in:draft,published,active',
                'type' => 'nullable|string',
                'meta' => 'nullable|array',
            ]);
            $page = $this->updatePageUseCase->execute($id, $data);
            if (!$page) {
                return response()->json(['success' => false, 'message' => 'Página no encontrada'], 404);
            }
            return response()->json(['success' => true, 'data' => $page]);
        } catch (\Exception $e) {
            Log::error('Error updating page: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar página: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar una página
     */
    public function delete(int $id): \Illuminate\Http\JsonResponse
    {
        try {
            $result = $this->deletePageUseCase->execute($id);
            if (!$result) {
                return response()->json(['success' => false, 'message' => 'Página no encontrada'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Página eliminada']);
        } catch (\Exception $e) {
            Log::error('Error deleting page: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar página'], 500);
        }
    }

    /**
     * Publicar una página (genera slug si no tiene)
     */
    public function publish(int $id): \Illuminate\Http\JsonResponse
    {
        try {
            $page = $this->publishPageUseCase->execute($id);
            if (!$page) {
                return response()->json(['success' => false, 'message' => 'Página no encontrada'], 404);
            }
            $publicUrl = $page->getPublicUrl();
            return response()->json([
                'success' => true,
                'data' => $page,
                'public_url' => $publicUrl,
            ]);
        } catch (\Exception $e) {
            Log::error('Error publishing page: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al publicar página'], 500);
        }
    }

    /**
     * Despublicar una página
     */
    public function unpublish(int $id): \Illuminate\Http\JsonResponse
    {
        try {
            $page = $this->publishPageUseCase->unpublish($id);
            if (!$page) {
                return response()->json(['success' => false, 'message' => 'Página no encontrada'], 404);
            }
            return response()->json(['success' => true, 'data' => $page]);
        } catch (\Exception $e) {
            Log::error('Error unpublishing page: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al despublicar página'], 500);
        }
    }

    /**
     * Obtener página pública por slug (renderiza HTML)
     */
    public function getPublicPage(string $slug): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
    {
        try {
            $page = $this->getPublicPageUseCase->execute($slug);
            if (!$page) {
                return response()->json(['success' => false, 'message' => 'Página no encontrada'], 404);
            }

            $html = $page->contentHtml ?? $page->content ?? '';

            return response($html)
                ->header('Content-Type', 'text/html');
        } catch (\Exception $e) {
            Log::error('Error serving public page: ' . $e->getMessage());
            abort(500, 'Error interno del servidor');
        }
    }
}
