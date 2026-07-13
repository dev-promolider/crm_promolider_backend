<?php

namespace Promolider\Application\Marketing\UseCases\Pages;

use Promolider\Domain\Marketing\Ports\Out\PageRepositoryInterface;

class DeletePageUseCase
{
    public function __construct(
        private readonly PageRepositoryInterface $pageRepository
    ) {}

    public function execute(int $pageId): bool
    {
        return $this->pageRepository->deletePage($pageId);
    }
}
