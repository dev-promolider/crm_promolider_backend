<?php

namespace Promolider\Domain\Marketing\Ports\Out;

use Promolider\Domain\Marketing\Entities\EditablePage;

interface EditablePageRepositoryInterface
{
    /** @return EditablePage[] */
    public function getAll(): array;

    /** @return EditablePage[] */
    public function getByUser(int $userId): array;

    public function getById(int $id): ?EditablePage;

    public function getPublicBySlug(string $slug): ?EditablePage;

    public function create(array $data): EditablePage;

    public function update(int $id, array $data): ?EditablePage;

    public function delete(int $id): bool;
}
