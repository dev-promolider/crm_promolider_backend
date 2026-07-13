<?php

namespace Promolider\Application\Marketing\UseCases\Gamification;

use Promolider\Domain\Marketing\Ports\Out\GamificationRepositoryInterface;

class InsertPointsUseCase
{
    public function __construct(
        private readonly GamificationRepositoryInterface $repository,
    ) {}

    public function execute(int $userId, int $incrementPoints, string $description): array
    {
        $userClassroomPointId = $this->repository->findOrCreateUserPointsByUser($userId);

        return $this->repository->insertPoints(
            $userClassroomPointId,
            $incrementPoints,
            $description
        );
    }
}
