<?php

namespace Promolider\Infrastructure\Marketing\Out\Persistence;

use App\Models\Badge as BadgeModel;
use App\Models\BadgeDetail;
use App\Models\ClassroomPointConfig as ClassroomPointConfigModel;
use App\Models\ClassroomPointDetail;
use App\Models\Reward as RewardModel;
use App\Models\RewardRedemption as RewardRedemptionModel;
use App\Models\UserClassroomPoint as UserClassroomPointModel;
use App\Models\UserLevel as UserLevelModel;
use Promolider\Domain\Marketing\Entities\Badge;
use Promolider\Domain\Marketing\Entities\ClassroomPointConfig;
use Promolider\Domain\Marketing\Entities\Reward;
use Promolider\Domain\Marketing\Entities\RewardRedemption;
use Promolider\Domain\Marketing\Entities\UserClassroomPoint;
use Promolider\Domain\Marketing\Entities\UserLevel;
use Promolider\Domain\Marketing\Ports\Out\GamificationRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EloquentGamificationRepository implements GamificationRepositoryInterface
{
    // ==================== RANKING ====================

    public function getRanking(int $limit = 10): array
    {
        $rows = UserClassroomPointModel::join('users', 'users.id', '=', 'user_classroom_points.id_user')
            ->orderBy('user_classroom_points.total_points', 'DESC')
            ->select('users.id', 'users.photo', 'users.name', 'users.last_name', 'user_classroom_points.total_points as total')
            ->limit($limit)
            ->get();

        return $rows->map(fn($r) => [
            'user_id' => $r->id,
            'name' => $r->name . ' ' . ($r->last_name ?? ''),
            'photo' => $r->photo,
            'total_points' => (int) $r->total,
        ])->toArray();
    }

    public function getUserPosition(int $userId): int
    {
        $ranking = UserClassroomPointModel::orderBy('total_points', 'DESC')->get()->toArray();
        $position = array_search($userId, array_column($ranking, 'id_user'));
        return $position !== false ? $position + 1 : 0;
    }

    public function getUserPoints(int $userId): int
    {
        $points = UserClassroomPointModel::select('total_points')->where('id_user', $userId)->first();
        return $points ? (int) $points->total_points : 0;
    }

    public function getPointsDetail(int $userId, int $limit = 5): array
    {
        $userPoints = UserClassroomPointModel::where('id_user', $userId)->first();
        if (!$userPoints) return [];

        return ClassroomPointDetail::where('id_user_classroom_points', $userPoints->id)
            ->orderBy('increment_points', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function findOrCreateUserPointsByUser(int $userId): int
    {
        $userPoints = UserClassroomPointModel::firstOrCreate(
            ['id_user' => $userId],
            ['total_points' => 0]
        );
        return $userPoints->id;
    }

    public function insertPoints(int $userClassroomPointId, int $incrementPoints, string $description): array
    {
        DB::beginTransaction();
        try {
            // Insert detail
            $detail = ClassroomPointDetail::create([
                'id_user_classroom_points' => $userClassroomPointId,
                'increment_points' => $incrementPoints,
                'description' => $description,
            ]);

            // Update total
            $userPoints = UserClassroomPointModel::findOrFail($userClassroomPointId);
            $userPoints->increment('total_points', $incrementPoints);
            $userPoints->refresh();

            DB::commit();

            return [
                'detail' => $detail->toArray(),
                'new_total' => (int) $userPoints->total_points,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // ==================== CONFIG ====================

    public function getConfigs(): array
    {
        return ClassroomPointConfigModel::all()->map(fn($c) => $c->toArray())->toArray();
    }

    public function updateConfig(int $id, array $data): ClassroomPointConfig
    {
        $model = ClassroomPointConfigModel::findOrFail($id);
        $model->update($data);
        $model->refresh();

        return $this->toConfigEntity($model);
    }

    private function toConfigEntity($model): ClassroomPointConfig
    {
        return new ClassroomPointConfig(
            id: $model->id,
            passedCourse: (float) $model->passed_course,
            dailyQuestion: (float) $model->daily_question,
            achievement: (float) $model->achievement,
        );
    }

    // ==================== LEVELS ====================

    public function getAllLevels(): array
    {
        return UserLevelModel::orderBy('experience_required', 'asc')->get()
            ->map(fn($l) => $this->toLevelEntity($l))
            ->toArray();
    }

    public function createLevel(array $data): UserLevel
    {
        $model = UserLevelModel::create($data);
        return $this->toLevelEntity($model);
    }

    public function updateLevel(int $id, array $data): ?UserLevel
    {
        $model = UserLevelModel::find($id);
        if (!$model) return null;

        $model->update($data);
        $model->refresh();
        return $this->toLevelEntity($model);
    }

    public function getLevelByPoints(int $points): ?UserLevel
    {
        $model = UserLevelModel::where('experience_required', '<=', $points)
            ->orderBy('experience_required', 'desc')
            ->first();

        return $model ? $this->toLevelEntity($model) : null;
    }

    public function getNextLevel(int $currentExpRequired): ?UserLevel
    {
        $model = UserLevelModel::where('experience_required', '>', $currentExpRequired)
            ->orderBy('experience_required', 'asc')
            ->first();

        return $model ? $this->toLevelEntity($model) : null;
    }

    private function toLevelEntity($model): UserLevel
    {
        return new UserLevel(
            id: $model->id,
            description: $model->description,
            experienceRequired: (int) $model->experience_required,
            urlIcon: $model->url_icon,
        );
    }

    // ==================== BADGES ====================

    public function getAllBadges(): array
    {
        return BadgeModel::orderBy('level')->get()
            ->map(fn($b) => $this->toBadgeEntity($b))
            ->toArray();
    }

    public function getBadgesWithUserStatus(int $userId): array
    {
        $badges = BadgeModel::orderBy('level')->get();
        $userBadgeIds = BadgeDetail::where('id_user', $userId)->pluck('id_badge')->toArray();

        return $badges->map(function ($badge) use ($userBadgeIds) {
            return new Badge(
                id: $badge->id,
                name: $badge->name,
                description: $badge->description,
                level: (int) $badge->level,
                condition: (int) $badge->condition,
                icon: $badge->icon,
                obtained: in_array($badge->id, $userBadgeIds),
            );
        })->toArray();
    }

    public function getUserBadges(int $userId): array
    {
        return BadgeModel::join('badge_detail', 'badges.id', '=', 'badge_detail.id_badge')
            ->where('badge_detail.id_user', $userId)
            ->select('badges.*')
            ->get()
            ->map(fn($b) => $this->toBadgeEntity($b))
            ->toArray();
    }

    public function createBadge(array $data): Badge
    {
        $model = BadgeModel::create($data);
        return $this->toBadgeEntity($model);
    }

    public function updateBadge(int $id, array $data): ?Badge
    {
        $model = BadgeModel::find($id);
        if (!$model) return null;

        $model->update($data);
        $model->refresh();
        return $this->toBadgeEntity($model);
    }

    public function deleteBadge(int $id): bool
    {
        $model = BadgeModel::find($id);
        if (!$model) return false;
        return $model->delete();
    }

    private function toBadgeEntity($model): Badge
    {
        return new Badge(
            id: $model->id,
            name: $model->name,
            description: $model->description,
            level: (int) $model->level,
            condition: (int) $model->condition,
            icon: $model->icon,
        );
    }

    // ==================== REWARDS ====================

    public function getAllRewards(bool $withTrashed = false): array
    {
        $query = RewardModel::query();
        if ($withTrashed) {
            $query->withTrashed();
        }

        return $query->withCount('redemptions')->orderBy('name')->get()
            ->map(fn($r) => $this->toRewardEntity($r))
            ->toArray();
    }

    public function getAvailableRewards(): array
    {
        return RewardModel::available()->orderBy('cost')->get()
            ->map(fn($r) => $this->toRewardEntity($r))
            ->toArray();
    }

    public function createReward(array $data): Reward
    {
        $model = RewardModel::create($data);
        return $this->toRewardEntity($model);
    }

    public function updateReward(int $id, array $data): ?Reward
    {
        $model = RewardModel::find($id);
        if (!$model) return null;

        $model->update($data);
        $model->refresh();
        return $this->toRewardEntity($model);
    }

    public function deleteReward(int $id): bool
    {
        $model = RewardModel::find($id);
        if (!$model) return false;
        return $model->delete();
    }

    public function restoreReward(int $id): bool
    {
        $model = RewardModel::withTrashed()->find($id);
        if (!$model) return false;
        return $model->restore();
    }

    public function getRewardStats(): array
    {
        $total = RewardModel::withTrashed()->count();
        $active = RewardModel::active()->count();
        $totalRedemptions = RewardRedemptionModel::count();
        $totalCreditsUsed = RewardRedemptionModel::where('status', 'processed')->sum('cost');
        $pending = RewardRedemptionModel::pending()->count();

        return [
            'total_rewards' => $total,
            'active_rewards' => $active,
            'total_redemptions' => $totalRedemptions,
            'total_credits_used' => (float) $totalCreditsUsed,
            'pending_redemptions' => $pending,
        ];
    }

    private function toRewardEntity($model): Reward
    {
        return new Reward(
            id: $model->id,
            name: $model->name,
            description: $model->description,
            cost: (float) $model->cost,
            stock: $model->stock,
            image: $model->image,
            active: (bool) $model->active,
            redemptionCount: $model->redemptions_count ?? null,
        );
    }

    // ==================== REDEMPTIONS ====================

    public function getRedemptions(array $filters = [], int $perPage = 15): array
    {
        $query = RewardRedemptionModel::with(['user', 'reward']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        $paginator = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return [
            'items' => collect($paginator->items())->map(fn($r) => $this->toRedemptionEntity($r))->toArray(),
            'total' => $paginator->total(),
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ];
    }

    public function getUserRedemptions(int $userId): array
    {
        return RewardRedemptionModel::with('reward')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($r) => $this->toRedemptionEntity($r))
            ->toArray();
    }

    public function getUserCredits(int $userId): float
    {
        $points = UserClassroomPointModel::select('total_points')->where('id_user', $userId)->first();
        return $points ? (float) $points->total_points : 0.0;
    }

    public function createRedemption(int $userId, int $rewardId, float $cost): RewardRedemption
    {
        $model = RewardRedemptionModel::create([
            'user_id' => $userId,
            'reward_id' => $rewardId,
            'cost' => $cost,
            'status' => 'pending',
        ]);

        // Decrement reward stock
        $reward = RewardModel::find($rewardId);
        if ($reward) {
            $reward->decrementStock();
        }

        return $this->toRedemptionEntity($model);
    }

    public function processRedemption(int $id, string $status, ?string $notes, ?int $processedBy): ?RewardRedemption
    {
        $model = RewardRedemptionModel::find($id);
        if (!$model) return null;

        DB::beginTransaction();
        try {
            if ($status === 'cancelled' && $model->status === 'pending') {
                // Refund credits and restore stock
                $reward = RewardModel::find($model->reward_id);
                if ($reward && !is_null($reward->stock)) {
                    $reward->increment('stock');
                }
            }

            $model->update([
                'status' => $status,
                'notes' => $notes,
                'processed_at' => now(),
                'processed_by' => $processedBy,
            ]);
            $model->refresh();

            DB::commit();
            return $this->toRedemptionEntity($model);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function findRewardById(int $id): ?Reward
    {
        $model = RewardModel::find($id);
        return $model ? $this->toRewardEntity($model) : null;
    }

    private function toRedemptionEntity($model): RewardRedemption
    {
        return new RewardRedemption(
            id: $model->id,
            userId: $model->user_id,
            rewardId: $model->reward_id,
            cost: (float) $model->cost,
            status: $model->status,
            notes: $model->notes,
            processedAt: $model->processed_at?->toIso8601String(),
            processedBy: $model->processed_by,
            userName: $model->relationLoaded('user') && $model->user ? $model->user->name : null,
            rewardName: $model->relationLoaded('reward') && $model->reward ? $model->reward->name : null,
        );
    }
}
