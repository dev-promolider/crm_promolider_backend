<?php

namespace Promolider\Domain\Infoproducts\Ports\Out;

interface CourseRepositoryInterface
{
    public function findModulesByCourseId(int $courseId): array;
}
