<?php

namespace Promolider\Infrastructure\Marketing\In\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Promolider\Application\Marketing\UseCases\Marketplace\GetMarketplaceItemsUseCase;
use Promolider\Application\Marketing\UseCases\Marketplace\ToggleMarketplaceVisibilityUseCase;
use Promolider\Domain\Marketing\Ports\Out\MarketplaceRepositoryInterface;

class MarketplaceController extends Controller
{
    public function __construct(
        private GetMarketplaceItemsUseCase $getMarketplaceItemsUseCase,
        private ToggleMarketplaceVisibilityUseCase $toggleMarketplaceVisibilityUseCase,
        private MarketplaceRepositoryInterface $marketplaceRepository,
    ) {}

    public function getMasterclasses(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $filters = $request->only(['category_id', 'search', 'page', 'per_page']);
            $data = $this->getMarketplaceItemsUseCase->getMasterclasses($filters);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error getting masterclasses: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener masterclasses'], 500);
        }
    }

    public function getEbooks(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $filters = $request->only(['category_id', 'search', 'page', 'per_page']);
            $data = $this->getMarketplaceItemsUseCase->getEbooks($filters);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error getting ebooks: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener ebooks'], 500);
        }
    }

    public function getMiniCourses(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $filters = $request->only(['category_id', 'search', 'page', 'per_page']);
            $data = $this->getMarketplaceItemsUseCase->getMiniCourses($filters);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error getting mini courses: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener mini cursos'], 500);
        }
    }

    public function getCampaigns(): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $this->getMarketplaceItemsUseCase->getCampaigns();
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error getting marketplace campaigns: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener campañas'], 500);
        }
    }

    public function toggleVisibility(int $courseId): \Illuminate\Http\JsonResponse
    {
        try {
            $result = $this->toggleMarketplaceVisibilityUseCase->execute($courseId);
            return response()->json(['success' => $result]);
        } catch (\Exception $e) {
            Log::error('Error toggling marketplace visibility: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al cambiar visibilidad'], 500);
        }
    }

    public function getMasterclassDetail(int $id): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $this->marketplaceRepository->getMasterclassDetail($id);
            if (!$data) {
                return response()->json(['success' => false, 'message' => 'Masterclass no encontrada'], 404);
            }
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error getting masterclass detail: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener detalle de masterclass'], 500);
        }
    }

    public function getEbookDetail(int $id): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $this->marketplaceRepository->getEbookDetail($id);
            if (!$data) {
                return response()->json(['success' => false, 'message' => 'Ebook no encontrado'], 404);
            }
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error getting ebook detail: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener detalle del ebook'], 500);
        }
    }

    public function getMiniCourseDetail(int $id): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $this->marketplaceRepository->getMiniCourseDetail($id);
            if (!$data) {
                return response()->json(['success' => false, 'message' => 'Mini curso no encontrado'], 404);
            }
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error getting mini course detail: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener detalle del mini curso'], 500);
        }
    }
}
