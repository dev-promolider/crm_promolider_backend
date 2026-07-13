<?php

namespace Promolider\Application\Marketing\UseCases\Pages;

use Promolider\Domain\Marketing\Ports\Out\PageRepositoryInterface;
use Promolider\Domain\Marketing\Entities\Page;

class UpdatePageUseCase
{
    public function __construct(
        private readonly PageRepositoryInterface $pageRepository
    ) {}

    public function execute(int $pageId, array $data): ?Page
    {
        return $this->pageRepository->updatePage($pageId, $data);
    }
}
