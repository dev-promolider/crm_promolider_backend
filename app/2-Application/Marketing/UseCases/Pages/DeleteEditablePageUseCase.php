<?php

namespace Promolider\Application\Marketing\UseCases\Pages;

use Promolider\Domain\Marketing\Ports\Out\EditablePageRepositoryInterface;

class DeleteEditablePageUseCase
{
    public function __construct(
        private EditablePageRepositoryInterface $repository,
    ) {}

    public function execute(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
