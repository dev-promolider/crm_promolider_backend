<?php

namespace Promolider\Domain\Infoproducts\Ports\Out;

use Promolider\Domain\Infoproducts\Entities\Infoproduct;

interface InfoproductRepositoryInterface
{
    public function findPurchasedByUserId(string $userId): array;

    public function findCreatedByUserIdPaginated(
        int $userId,
        int $page,
        int $perPage,
        ?string $search = null,
        ?int $productTypeId = null,
    ): array;

    public function findCourseById(int $courseId): ?Infoproduct;
}
