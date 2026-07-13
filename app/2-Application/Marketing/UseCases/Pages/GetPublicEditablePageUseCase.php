<?php

namespace Promolider\Application\Marketing\UseCases\Pages;

use Promolider\Domain\Marketing\Ports\Out\EditablePageRepositoryInterface;

class GetPublicEditablePageUseCase
{
    public function __construct(
        private readonly EditablePageRepositoryInterface $repository,
    ) {}

    public function execute(string $slug): ?\Promolider\Domain\Marketing\Entities\EditablePage
    {
        return $this->repository->getPublicBySlug($slug);
    }
}
