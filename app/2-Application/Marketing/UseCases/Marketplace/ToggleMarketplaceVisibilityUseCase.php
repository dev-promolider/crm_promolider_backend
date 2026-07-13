<?php

namespace Promolider\Application\Marketing\UseCases\Marketplace;

use Promolider\Domain\Marketing\Ports\Out\MarketplaceRepositoryInterface;

class ToggleMarketplaceVisibilityUseCase
{
    public function __construct(
        private readonly MarketplaceRepositoryInterface $marketplaceRepository
    ) {}

    public function execute(int $courseId): bool
    {
        return $this->marketplaceRepository->toggleMarketplaceVisibility($courseId);
    }
}
