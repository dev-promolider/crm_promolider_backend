<?php
namespace Promolider\Application\Dashboard\UseCases;

use Promolider\Domain\Dashboard\Ports\Out\DashboardRepositoryInterface;

class GetBinaryTreeUseCase
{
    public function __construct(
        private DashboardRepositoryInterface $dashboardRepository
    ) {}

    public function execute(int $userId): array
    {
        return $this->dashboardRepository->getBinaryTree($userId);
    }
}
