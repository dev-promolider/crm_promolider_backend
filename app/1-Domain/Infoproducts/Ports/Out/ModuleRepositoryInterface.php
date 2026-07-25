<?php

namespace Promolider\Domain\Infoproducts\Ports\Out;

use Promolider\Domain\Infoproducts\Entities\Course\Module;

interface ModuleRepositoryInterface
{
    public function findById(int $moduleId): ?Module;
    public function findByCourseId(int $courseId): array;
    public function findByIdAndCourseId(int $moduleId, int $courseId): ?Module;
    public function updateModuleStatus(int $moduleId, int $status): void;
}
