<?php

namespace Promolider\Application\Marketing\UseCases\Gamification;

use Promolider\Domain\Marketing\Ports\Out\GamificationRepositoryInterface;

class UserRewardsUseCase
{
    public function __construct(
        private GamificationRepositoryInterface $repository,
    ) {}

    public function getAvailableRewards(): array
    {
        $rewards = $this->repository->getAvailableRewards();
        return array_map(fn($r) => $r->toArray(), $rewards);
    }

    public function getCredits(int $userId): float
    {
        return $this->repository->getUserCredits($userId);
    }

    public function redeem(int $userId, int $rewardId): array
    {
        $reward = $this->repository->findRewardById($rewardId);
        if (!$reward || !$reward->isActive() || !$reward->hasStock()) {
            return ['success' => false, 'message' => 'Recompensa no disponible'];
        }

        $credits = $this->repository->getUserCredits($userId);
        if ($credits < $reward->getCost()) {
            return ['success' => false, 'message' => 'Créditos insuficientes'];
        }

        $redemption = $this->repository->createRedemption($userId, $rewardId, $reward->getCost());
        return [
            'success' => true,
            'message' => 'Canje realizado exitosamente',
            'data' => $redemption->toArray(),
        ];
    }

    public function getMyRedemptions(int $userId): array
    {
        return $this->repository->getUserRedemptions($userId);
    }
}
