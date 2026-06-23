<?php
namespace Promolider\Application\Dashboard\UseCases;

use Promolider\Domain\Dashboard\Ports\Out\DashboardRepositoryInterface;

class GetTopbarStatsUseCase
{
    public function __construct(
        private DashboardRepositoryInterface $dashboardRepository
    ) {}

    public function execute(int $userId): array
    {
        return $this->dashboardRepository->getTopbarStats($userId);
    }
}
