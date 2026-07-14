<?php

namespace Promolider\Application\Marketing\UseCases\Pages;

use Promolider\Domain\Marketing\Ports\Out\PageRepositoryInterface;
use Promolider\Domain\Marketing\Entities\Page;

class PublishPageUseCase
{
    public function __construct(
        private PageRepositoryInterface $pageRepository
    ) {}

    public function execute(int $pageId): ?Page
    {
        return $this->pageRepository->publishPage($pageId);
    }

    public function unpublish(int $pageId): ?Page
    {
        return $this->pageRepository->unpublishPage($pageId);
    }
}
