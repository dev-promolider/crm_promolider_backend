<?php

namespace Promolider\Application\Marketing\UseCases\Marketplace;

use Promolider\Domain\Marketing\Ports\Out\MarketplaceRepositoryInterface;

class GetMarketplaceItemsUseCase
{
    public function __construct(
        private MarketplaceRepositoryInterface $marketplaceRepository
    ) {}

    public function getMasterclasses(array $filters = []): array
    {
        return $this->marketplaceRepository->getMasterclasses($filters);
    }

    public function getEbooks(array $filters = []): array
    {
        return $this->marketplaceRepository->getEbooks($filters);
    }

    public function getMiniCourses(array $filters = []): array
    {
        return $this->marketplaceRepository->getMiniCourses($filters);
    }

    public function getCampaigns(): array
    {
        return $this->marketplaceRepository->getCampaigns();
    }
}
