<?php

namespace Promolider\Domain\Marketing\Ports\Out;

use Promolider\Domain\Marketing\Entities\ProductDistributor;

interface ProductDistributorRepositoryInterface
{
    public function createInvitation(string $productType, int $productId, int $userId, string $code, string $expiresAt): ProductDistributor;

    public function findExistingInvitation(string $productType, int $productId, int $userId): ?ProductDistributor;

    public function findByCode(string $productType, string $code): ?ProductDistributor;
}
