<?php

namespace Promolider\Application\Marketing\UseCases\Pages;

use Promolider\Domain\Marketing\Ports\Out\PageRepositoryInterface;
use Promolider\Domain\Marketing\Entities\Page;

class GetPublicPageUseCase
{
    public function __construct(
        private PageRepositoryInterface $pageRepository
    ) {}

    public function execute(string $slug): ?Page
    {
        return $this->pageRepository->getPublicPage($slug);
    }
}
