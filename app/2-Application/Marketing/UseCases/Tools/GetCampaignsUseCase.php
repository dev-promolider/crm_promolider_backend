<?php

namespace Promolider\Application\Marketing\UseCases\Tools;

use Promolider\Domain\Marketing\Ports\Out\ToolRepositoryInterface;

class GetCampaignsUseCase
{
    public function __construct(
        private readonly ToolRepositoryInterface $toolRepository
    ) {}

    public function execute(): array
    {
        return $this->toolRepository->getCampaigns();
    }

    public function getByType(string $type): array
    {
        return $this->toolRepository->getCampaignsByType($type);
    }
}
