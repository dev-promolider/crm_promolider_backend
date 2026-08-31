<?php

namespace Promolider\Infrastructure\Marketing\In\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Promolider\Application\Marketing\UseCases\Marketplace\GetMarketplaceItemsUseCase;
use Promolider\Application\Marketing\UseCases\Marketplace\ToggleMarketplaceVisibilityUseCase;
use Promolider\Domain\Marketing\Ports\Out\MarketplaceRepositoryInterface;
use App\Models\DistributorToolUsage;

class MarketplaceController extends Controller
{
    public function __construct(
        private GetMarketplaceItemsUseCase $getMarketplaceItemsUseCase,
        private ToggleMarketplaceVisibilityUseCase $toggleMarketplaceVisibilityUseCase,
        private MarketplaceRepositoryInterface $marketplaceRepository,
    ) {}

    public function getCourses(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $filters = $request->only(['category_id', 'search', 'page', 'per_page']);
            $data = $this->getMarketplaceItemsUseCase->getCourses($filters);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error getting courses: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener cursos'], 500);
        }
    }

    public function getCourseResources(int $courseId): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $this->getMarketplaceItemsUseCase->getCourseResources($courseId);
            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            Log::error('Error getting course resources: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener recursos del curso'], 500);
        }
    }

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

    public function activateToolUsage(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $request->validate([
                'type' => 'required|string',
                'id' => 'required|integer',
            ]);

            $type = $request->type;
            $id = $request->id;
            
            $modelClass = match($type) {
                'masterclass' => \App\Models\Masterclass::class,
                'ebook' => \App\Models\Ebook::class,
                'minicourse' => \App\Models\Minicourse::class,
                'material', 'promotional' => \App\Models\MarketingMaterial::class,
                default => null,
            };

            if (!$modelClass) {
                return response()->json(['success' => false, 'message' => 'Tipo de herramienta inválido'], 400);
            }

            $usage = DistributorToolUsage::firstOrCreate([
                'user_id' => $request->user()->id,
                'usageable_type' => $modelClass,
                'usageable_id' => $id,
            ]);

            if ($request->has('url')) {
                $usage->generated_link = $request->url;
                $usage->save();
            }

            $affiliateLink = $usage->generated_link ?: url("/ref/" . $request->user()->code . "/$type/$id");

            return response()->json([
                'success' => true,
                'affiliate_link' => $affiliateLink,
            ]);
        } catch (\Exception $e) {
            Log::error('Error activating tool usage: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al activar la herramienta'], 500);
        }
    }

    public function getMyUsages(Request $request, $courseId): \Illuminate\Http\JsonResponse
    {
        try {
            $user = $request->user();
            $usages = DistributorToolUsage::where('user_id', $user->id)
                ->get();

            $links = [];
            foreach ($usages as $usage) {
                $type = match($usage->usageable_type) {
                    \App\Models\Masterclass::class => 'masterclass',
                    \App\Models\Ebook::class => 'ebook',
                    \App\Models\Minicourse::class => 'minicourse',
                    \App\Models\MarketingMaterial::class => 'material',
                    default => null,
                };

                if ($type) {
                    $links[$usage->usageable_id] = [
                        'type' => $type,
                        'id' => $usage->usageable_id,
                        'affiliate_link' => $usage->generated_link ?: url("/ref/" . $user->code . "/$type/" . $usage->usageable_id),
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $links,
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting my usages: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener usos'], 500);
        }
    }
}
