<?php

namespace Promolider\Application\Marketing\UseCases\Gamification;

use Promolider\Domain\Marketing\Ports\Out\GamificationRepositoryInterface;

class ManageLevelsUseCase
{
    public function __construct(
        private GamificationRepositoryInterface $repository,
    ) {}

    public function listAll(): array
    {
        $levels = $this->repository->getAllLevels();
        return array_map(fn($l) => $l->toArray(), $levels);
    }

    public function create(array $data): array
    {
        $level = $this->repository->createLevel($data);
        return $level->toArray();
    }

    public function update(int $id, array $data): ?array
    {
        $level = $this->repository->updateLevel($id, $data);
        return $level?->toArray();
    }
}
