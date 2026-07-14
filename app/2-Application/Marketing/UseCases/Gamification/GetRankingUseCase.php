<?php

namespace Promolider\Application\Marketing\UseCases\Gamification;

use Promolider\Domain\Marketing\Ports\Out\GamificationRepositoryInterface;

class GetRankingUseCase
{
    public function __construct(
        private GamificationRepositoryInterface $repository,
    ) {}

    public function execute(int $userId): array
    {
        $ranking = $this->repository->getRanking(10);
        $position = $this->repository->getUserPosition($userId);

        return [
            'ranking' => $ranking,
            'my_position' => $position,
        ];
    }
}
