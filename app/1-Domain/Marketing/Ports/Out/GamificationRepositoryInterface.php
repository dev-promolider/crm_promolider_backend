<?php

namespace Promolider\Domain\Marketing\Ports\Out;

use Promolider\Domain\Marketing\Entities\ClassroomPointConfig;
use Promolider\Domain\Marketing\Entities\UserLevel;
use Promolider\Domain\Marketing\Entities\Badge;
use Promolider\Domain\Marketing\Entities\Reward;
use Promolider\Domain\Marketing\Entities\RewardRedemption;

interface GamificationRepositoryInterface
{
    // === User Classroom Points (Ranking) ===
    public function getRanking(int $limit = 10): array;
    public function getUserPosition(int $userId): int;
    public function getUserPoints(int $userId): int;
    public function getPointsDetail(int $userId, int $limit = 5): array;
    public function findOrCreateUserPointsByUser(int $userId): int;
    public function insertPoints(int $userClassroomPointId, int $incrementPoints, string $description): array;

    // === Classroom Point Config ===
    public function getConfigs(): array;
    public function updateConfig(int $id, array $data): ClassroomPointConfig;

    // === User Levels ===
    public function getAllLevels(): array;
    public function createLevel(array $data): UserLevel;
    public function updateLevel(int $id, array $data): ?UserLevel;
    public function getLevelByPoints(int $points): ?UserLevel;
    public function getNextLevel(int $currentExpRequired): ?UserLevel;

    // === Badges ===
    public function getAllBadges(): array;
    public function getBadgesWithUserStatus(int $userId): array;
    public function getUserBadges(int $userId): array;
    public function createBadge(array $data): Badge;
    public function updateBadge(int $id, array $data): ?Badge;
    public function deleteBadge(int $id): bool;

    // === Rewards ===
    public function getAllRewards(bool $withTrashed = false): array;
    public function getAvailableRewards(): array;
    public function createReward(array $data): Reward;
    public function updateReward(int $id, array $data): ?Reward;
    public function deleteReward(int $id): bool;
    public function restoreReward(int $id): bool;
    public function getRewardStats(): array;

    // === Reward Redemptions ===
    public function getRedemptions(array $filters = [], int $perPage = 15): array;
    public function getUserRedemptions(int $userId): array;
    public function getUserCredits(int $userId): float;
    public function createRedemption(int $userId, int $rewardId, float $cost): RewardRedemption;
    public function processRedemption(int $id, string $status, ?string $notes, ?int $processedBy): ?RewardRedemption;
    public function findRewardById(int $id): ?Reward;
}
