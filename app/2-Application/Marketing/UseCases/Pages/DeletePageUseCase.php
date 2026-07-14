<?php

namespace Promolider\Application\Marketing\UseCases\Pages;

use Promolider\Domain\Marketing\Ports\Out\PageRepositoryInterface;

class DeletePageUseCase
{
    public function __construct(
        private PageRepositoryInterface $pageRepository
    ) {}

    public function execute(int $pageId): bool
    {
        return $this->pageRepository->deletePage($pageId);
    }
}
