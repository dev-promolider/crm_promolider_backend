<?php
namespace Promolider\Domain\Dashboard\Ports\Out;

interface DashboardRepositoryInterface
{
    public function getTopbarStats(int $userId): array;
    public function getWidgetsData(int $userId, string $timeframe = 'normal'): array;
    public function getUnilevelTree(int $userId): array;
    public function getBinaryTree(int $userId): array;
}
