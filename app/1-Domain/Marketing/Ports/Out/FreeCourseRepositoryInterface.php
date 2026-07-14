<?php

namespace Promolider\Domain\Marketing\Ports\Out;

use Promolider\Domain\Marketing\Entities\FreeCourse;

interface FreeCourseRepositoryInterface
{
    public function list(array $filters = []): array;

    public function findById(int $id): ?FreeCourse;

    public function create(array $data): FreeCourse;

    public function delete(int $id): bool;
}
