<?php

namespace Promolider\Domain\Marketing\Ports\Out;

use Promolider\Domain\Marketing\Entities\PaymentLink;

interface PaymentLinkRepositoryInterface
{
    public function list(array $filters = []): array;

    public function findById(int $id): ?PaymentLink;

    public function findBySlug(string $slug): ?PaymentLink;

    public function create(array $data): PaymentLink;

    public function update(int $id, array $data): PaymentLink;

    public function toggleStatus(int $id): PaymentLink;

    public function delete(int $id): bool;

    public function incrementUsage(int $id): void;
}
