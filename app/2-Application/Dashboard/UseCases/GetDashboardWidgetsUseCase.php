<?php
namespace Promolider\Application\Dashboard\UseCases;

use Promolider\Domain\Dashboard\Ports\Out\DashboardRepositoryInterface;

class GetDashboardWidgetsUseCase
{
    public function __construct(
        private DashboardRepositoryInterface $dashboardRepository
    ) {}

    public function execute(int $userId, string $timeframe = 'normal'): array
    {
        return $this->dashboardRepository->getWidgetsData($userId, $timeframe);
    }
}
