<?php

namespace Promolider\Infrastructure\Marketing\In\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Promolider\Application\Marketing\UseCases\Gamification\GetRankingUseCase;
use Promolider\Application\Marketing\UseCases\Gamification\GetUserGamificationInfoUseCase;
use Promolider\Application\Marketing\UseCases\Gamification\ManageConfigUseCase;
use Promolider\Application\Marketing\UseCases\Gamification\ManageLevelsUseCase;
use Promolider\Application\Marketing\UseCases\Gamification\ManageBadgesUseCase;
use Promolider\Application\Marketing\UseCases\Gamification\ManageRewardsUseCase;
use Promolider\Application\Marketing\UseCases\Gamification\UserRewardsUseCase;
use Promolider\Application\Marketing\UseCases\Gamification\InsertPointsUseCase;

class GamificationController extends Controller
{
    public function __construct(
        private readonly GetRankingUseCase $getRankingUseCase,
        private readonly GetUserGamificationInfoUseCase $getUserGamificationInfoUseCase,
        private readonly ManageConfigUseCase $manageConfigUseCase,
        private readonly ManageLevelsUseCase $manageLevelsUseCase,
        private readonly ManageBadgesUseCase $manageBadgesUseCase,
        private readonly ManageRewardsUseCase $manageRewardsUseCase,
        private readonly UserRewardsUseCase $userRewardsUseCase,
        private readonly InsertPointsUseCase $insertPointsUseCase,
    ) {}

    // ==================== RANKING ====================

    public function ranking(Request $request): JsonResponse
    {
        try {
            $result = $this->getRankingUseCase->execute($request->user()->id);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('Error en ranking: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener ranking'], 500);
        }
    }

    // ==================== USER INFO ====================

    public function myInfo(Request $request): JsonResponse
    {
        try {
            $result = $this->getUserGamificationInfoUseCase->execute($request->user()->id);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('Error en myInfo: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener información'], 500);
        }
    }

    // ==================== CONFIG ====================

    public function getConfig(): JsonResponse
    {
        try {
            $configs = $this->manageConfigUseCase->getConfigs();
            return response()->json(['success' => true, 'data' => $configs]);
        } catch (\Exception $e) {
            Log::error('Error en getConfig: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener configuración'], 500);
        }
    }

    public function updateConfig(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'passed_course' => 'nullable|numeric|min:0',
                'daily_question' => 'nullable|numeric|min:0',
                'achievement' => 'nullable|numeric|min:0',
            ]);

            $result = $this->manageConfigUseCase->updateConfig($id, $validated);
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('Error en updateConfig: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar configuración'], 500);
        }
    }

    // ==================== LEVELS ====================

    public function levelsIndex(): JsonResponse
    {
        try {
            $levels = $this->manageLevelsUseCase->listAll();
            return response()->json(['success' => true, 'data' => $levels]);
        } catch (\Exception $e) {
            Log::error('Error en levelsIndex: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener niveles'], 500);
        }
    }

    public function levelsStore(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'description' => 'required|string|max:255|unique:user_levels,description',
                'experience_required' => 'required|integer|min:0|unique:user_levels,experience_required',
                'url_icon' => 'nullable|string|max:255',
            ]);

            $result = $this->manageLevelsUseCase->create($validated);
            return response()->json(['success' => true, 'data' => $result], 201);
        } catch (\Exception $e) {
            Log::error('Error en levelsStore: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear nivel'], 500);
        }
    }

    public function levelsUpdate(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'description' => 'sometimes|string|max:255|unique:user_levels,description,' . $id,
                'experience_required' => 'sometimes|integer|min:0|unique:user_levels,experience_required,' . $id,
                'url_icon' => 'nullable|string|max:255',
            ]);

            $result = $this->manageLevelsUseCase->update($id, $validated);
            if (!$result) {
                return response()->json(['success' => false, 'message' => 'Nivel no encontrado'], 404);
            }
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('Error en levelsUpdate: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar nivel'], 500);
        }
    }

    // ==================== BADGES ====================

    public function badgesIndex(Request $request): JsonResponse
    {
        try {
            $badges = $this->manageBadgesUseCase->listAll($request->user()->id);
            return response()->json(['success' => true, 'data' => $badges]);
        } catch (\Exception $e) {
            Log::error('Error en badgesIndex: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener insignias'], 500);
        }
    }

    public function myBadges(Request $request): JsonResponse
    {
        try {
            $badges = $this->manageBadgesUseCase->getUserBadges($request->user()->id);
            return response()->json(['success' => true, 'data' => $badges]);
        } catch (\Exception $e) {
            Log::error('Error en myBadges: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener mis insignias'], 500);
        }
    }

    public function badgesStore(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'level' => 'required|integer|min:1',
                'condition' => 'required|integer|min:1',
                'icon' => 'required|string|max:255',
            ]);

            $result = $this->manageBadgesUseCase->create($validated);
            return response()->json(['success' => true, 'data' => $result], 201);
        } catch (\Exception $e) {
            Log::error('Error en badgesStore: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear insignia'], 500);
        }
    }

    public function badgesUpdate(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'level' => 'sometimes|integer|min:1',
                'condition' => 'sometimes|integer|min:1',
                'icon' => 'sometimes|string|max:255',
            ]);

            $result = $this->manageBadgesUseCase->update($id, $validated);
            if (!$result) {
                return response()->json(['success' => false, 'message' => 'Insignia no encontrada'], 404);
            }
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('Error en badgesUpdate: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar insignia'], 500);
        }
    }

    public function badgesDestroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->manageBadgesUseCase->delete($id);
            if (!$deleted) {
                return response()->json(['success' => false, 'message' => 'Insignia no encontrada'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Insignia eliminada']);
        } catch (\Exception $e) {
            Log::error('Error en badgesDestroy: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar insignia'], 500);
        }
    }

    // ==================== REWARDS (Admin) ====================

    public function rewardsIndex(): JsonResponse
    {
        try {
            $rewards = $this->manageRewardsUseCase->listAll();
            return response()->json(['success' => true, 'data' => $rewards]);
        } catch (\Exception $e) {
            Log::error('Error en rewardsIndex: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener recompensas'], 500);
        }
    }

    public function rewardsStore(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'cost' => 'required|numeric|min:0',
                'stock' => 'nullable|integer|min:0',
                'image' => 'required|string|max:255',
                'active' => 'boolean',
            ]);

            $result = $this->manageRewardsUseCase->create($validated);
            return response()->json(['success' => true, 'data' => $result], 201);
        } catch (\Exception $e) {
            Log::error('Error en rewardsStore: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al crear recompensa'], 500);
        }
    }

    public function rewardsUpdate(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'cost' => 'sometimes|numeric|min:0',
                'stock' => 'nullable|integer|min:0',
                'image' => 'sometimes|string|max:255',
                'active' => 'boolean',
            ]);

            $result = $this->manageRewardsUseCase->update($id, $validated);
            if (!$result) {
                return response()->json(['success' => false, 'message' => 'Recompensa no encontrada'], 404);
            }
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('Error en rewardsUpdate: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar recompensa'], 500);
        }
    }

    public function rewardsDestroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->manageRewardsUseCase->delete($id);
            if (!$deleted) {
                return response()->json(['success' => false, 'message' => 'Recompensa no encontrada'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Recompensa eliminada']);
        } catch (\Exception $e) {
            Log::error('Error en rewardsDestroy: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al eliminar recompensa'], 500);
        }
    }

    public function rewardsRestore(int $id): JsonResponse
    {
        try {
            $restored = $this->manageRewardsUseCase->restore($id);
            if (!$restored) {
                return response()->json(['success' => false, 'message' => 'Recompensa no encontrada'], 404);
            }
            return response()->json(['success' => true, 'message' => 'Recompensa restaurada']);
        } catch (\Exception $e) {
            Log::error('Error en rewardsRestore: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al restaurar recompensa'], 500);
        }
    }

    public function rewardsStats(): JsonResponse
    {
        try {
            $stats = $this->manageRewardsUseCase->stats();
            return response()->json(['success' => true, 'data' => $stats]);
        } catch (\Exception $e) {
            Log::error('Error en rewardsStats: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener estadísticas'], 500);
        }
    }

    public function redemptionsIndex(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['status', 'user_id']);
            $redemptions = $this->manageRewardsUseCase->getRedemptions($filters);
            return response()->json(['success' => true, 'data' => $redemptions]);
        } catch (\Exception $e) {
            Log::error('Error en redemptionsIndex: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener canjes'], 500);
        }
    }

    public function processRedemption(Request $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'status' => 'required|in:processed,cancelled',
                'notes' => 'nullable|string',
            ]);

            $result = $this->manageRewardsUseCase->processRedemption(
                $id,
                $validated['status'],
                $validated['notes'] ?? null,
                $request->user()->id
            );

            if (!$result) {
                return response()->json(['success' => false, 'message' => 'Canje no encontrado'], 404);
            }
            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Exception $e) {
            Log::error('Error en processRedemption: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al procesar canje'], 500);
        }
    }

    // ==================== USER REWARDS ====================

    public function availableRewards(): JsonResponse
    {
        try {
            $rewards = $this->userRewardsUseCase->getAvailableRewards();
            return response()->json(['success' => true, 'data' => $rewards]);
        } catch (\Exception $e) {
            Log::error('Error en availableRewards: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener recompensas'], 500);
        }
    }

    public function myCredits(Request $request): JsonResponse
    {
        try {
            $credits = $this->userRewardsUseCase->getCredits($request->user()->id);
            return response()->json(['success' => true, 'data' => ['credits' => $credits]]);
        } catch (\Exception $e) {
            Log::error('Error en myCredits: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener créditos'], 500);
        }
    }

    public function redeemReward(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'reward_id' => 'required|integer|exists:rewards,id',
            ]);

            $result = $this->userRewardsUseCase->redeem($request->user()->id, $validated['reward_id']);
            $status = $result['success'] ? 200 : 400;
            return response()->json($result, $status);
        } catch (\Exception $e) {
            Log::error('Error en redeemReward: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al canjear recompensa'], 500);
        }
    }

    public function myRedemptions(Request $request): JsonResponse
    {
        try {
            $redemptions = $this->userRewardsUseCase->getMyRedemptions($request->user()->id);
            return response()->json(['success' => true, 'data' => $redemptions]);
        } catch (\Exception $e) {
            Log::error('Error en myRedemptions: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al obtener mis canjes'], 500);
        }
    }

    // ==================== MANUAL POINT INSERTION ====================

    public function insertPoints(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|integer|exists:users,id',
                'increment_points' => 'required|integer|min:1',
                'description' => 'required|string|max:255',
            ]);

            $result = $this->insertPointsUseCase->execute(
                $validated['user_id'],
                $validated['increment_points'],
                $validated['description']
            );

            return response()->json(['success' => true, 'data' => $result], 201);
        } catch (\Exception $e) {
            Log::error('Error en insertPoints: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al insertar puntos'], 500);
        }
    }
}
