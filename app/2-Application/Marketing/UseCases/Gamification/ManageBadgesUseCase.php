<?php

namespace Promolider\Application\Marketing\UseCases\Gamification;

use Promolider\Domain\Marketing\Ports\Out\GamificationRepositoryInterface;

class ManageBadgesUseCase
{
    public function __construct(
        private readonly GamificationRepositoryInterface $repository,
    ) {}

    public function listAll(int $userId): array
    {
        return $this->repository->getBadgesWithUserStatus($userId);
    }

    public function getUserBadges(int $userId): array
    {
        return $this->repository->getUserBadges($userId);
    }

    public function create(array $data): array
    {
        $badge = $this->repository->createBadge($data);
        return $badge->toArray();
    }

    public function update(int $id, array $data): ?array
    {
        $badge = $this->repository->updateBadge($id, $data);
        return $badge?->toArray();
    }

    public function delete(int $id): bool
    {
        return $this->repository->deleteBadge($id);
    }
}
