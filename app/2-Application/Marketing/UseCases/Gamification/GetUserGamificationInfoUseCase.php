<?php

namespace Promolider\Application\Marketing\UseCases\Gamification;

use Promolider\Domain\Marketing\Ports\Out\GamificationRepositoryInterface;

class GetUserGamificationInfoUseCase
{
    public function __construct(
        private readonly GamificationRepositoryInterface $repository,
    ) {}

    public function execute(int $userId): array
    {
        $points = $this->repository->getUserPoints($userId);
        $currentLevel = $this->repository->getLevelByPoints($points);
        $nextLevel = null;
        $percentage = 100;

        if ($currentLevel) {
            $nextLevel = $this->repository->getNextLevel($currentLevel->getExperienceRequired());
            if ($nextLevel && $nextLevel->getExperienceRequired() > 0) {
                $percentage = ($points / $nextLevel->getExperienceRequired()) * 100;
            }
        }

        $pointsDetail = $this->repository->getPointsDetail($userId, 5);

        return [
            'total_points' => $points,
            'current_level' => $currentLevel?->toArray(),
            'next_level' => $nextLevel?->toArray(),
            'progress_percentage' => min(100, $percentage),
            'points_detail' => $pointsDetail,
        ];
    }
}
