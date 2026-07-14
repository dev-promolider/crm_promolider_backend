<?php

namespace Promolider\Application\Marketing\UseCases\Gamification;

use Promolider\Domain\Marketing\Ports\Out\GamificationRepositoryInterface;

class ManageConfigUseCase
{
    public function __construct(
        private GamificationRepositoryInterface $repository,
    ) {}

    public function getConfigs(): array
    {
        return $this->repository->getConfigs();
    }

    public function updateConfig(int $id, array $data): array
    {
        $config = $this->repository->updateConfig($id, $data);
        return $config->toArray();
    }
}
