<?php

namespace Promolider\Application\Marketing\UseCases\Pages;

use Promolider\Domain\Marketing\Ports\Out\PageRepositoryInterface;

class GetTemplatesUseCase
{
    public function __construct(
        private PageRepositoryInterface $pageRepository
    ) {}

    public function execute(): array
    {
        return $this->pageRepository->getTemplates();
    }

    public function getUserPages(int $userId): array
    {
        return $this->pageRepository->getUserPages($userId);
    }

    public function getPage(int $pageId): ?\Promolider\Domain\Marketing\Entities\Page
    {
        return $this->pageRepository->getPage($pageId);
    }
}
