<?php

namespace Promolider\Domain\Marketing\Ports\Out;

use Promolider\Domain\Marketing\Entities\Page;

interface PageRepositoryInterface
{
    /** @return Page[] */
    public function getTemplates(): array;

    /** @return Page[] */
    public function getUserPages(int $userId): array;

    public function getPage(int $pageId): ?Page;

    public function getPublicPage(string $slug): ?Page;

    public function createPage(array $data): Page;

    public function updatePage(int $pageId, array $data): ?Page;

    public function deletePage(int $pageId): bool;

    public function publishPage(int $pageId): ?Page;

    public function unpublishPage(int $pageId): ?Page;
}
