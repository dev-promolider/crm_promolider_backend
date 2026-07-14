<?php

namespace Promolider\Application\Marketing\UseCases\Pages;

use Promolider\Domain\Marketing\Ports\Out\EditablePageRepositoryInterface;

class ListEditablePagesUseCase
{
    public function __construct(
        private EditablePageRepositoryInterface $repository,
    ) {}

    public function execute(): array
    {
        return $this->repository->getAll();
    }

    public function getByUser(int $userId): array
    {
        return $this->repository->getByUser($userId);
    }

    public function getById(int $id): ?\Promolider\Domain\Marketing\Entities\EditablePage
    {
        return $this->repository->getById($id);
    }
}
