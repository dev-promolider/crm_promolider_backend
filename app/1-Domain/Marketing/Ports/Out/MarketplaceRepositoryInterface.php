<?php

namespace Promolider\Domain\Marketing\Ports\Out;

interface MarketplaceRepositoryInterface
{
    /** @return array */
    public function getMarketplaceItems(string $type, array $filters = []): array;

    /** @return array */
    public function getMasterclasses(array $filters = []): array;

    /** @return array */
    public function getEbooks(array $filters = []): array;

    /** @return array */
    public function getMiniCourses(array $filters = []): array;

    /** @return array */
    public function getCampaigns(): array;

    public function toggleMarketplaceVisibility(int $courseId): bool;

    /** @return array */
    public function getCourseSubscribers(int $courseId): int;

    /** @return array|null */
    public function getMasterclassDetail(int $id): ?array;

    /** @return array|null */
    public function getEbookDetail(int $id): ?array;

    /** @return array|null */
    public function getMiniCourseDetail(int $id): ?array;
}
