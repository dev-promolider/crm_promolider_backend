<?php

namespace Promolider\Application\Marketing\UseCases\Gamification;

use Promolider\Domain\Marketing\Ports\Out\GamificationRepositoryInterface;

class ManageRewardsUseCase
{
    public function __construct(
        private GamificationRepositoryInterface $repository,
    ) {}

    public function listAll(): array
    {
        $rewards = $this->repository->getAllRewards(true);
        return array_map(fn($r) => $r->toArray(), $rewards);
    }

    public function create(array $data): array
    {
        $reward = $this->repository->createReward($data);
        return $reward->toArray();
    }

    public function update(int $id, array $data): ?array
    {
        $reward = $this->repository->updateReward($id, $data);
        return $reward?->toArray();
    }

    public function delete(int $id): bool
    {
        return $this->repository->deleteReward($id);
    }

    public function restore(int $id): bool
    {
        return $this->repository->restoreReward($id);
    }

    public function stats(): array
    {
        return $this->repository->getRewardStats();
    }

    public function getRedemptions(array $filters = []): array
    {
        return $this->repository->getRedemptions($filters);
    }

    public function processRedemption(int $id, string $status, ?string $notes, ?int $processedBy): ?array
    {
        $redemption = $this->repository->processRedemption($id, $status, $notes, $processedBy);
        return $redemption?->toArray();
    }
}
