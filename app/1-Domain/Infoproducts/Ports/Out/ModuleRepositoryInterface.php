<?php

namespace Promolider\Domain\Infoproducts\Ports\Out;

use Promolider\Domain\Infoproducts\Entities\Course\Module as ModuleEntity;

interface ModuleRepositoryInterface
{
    public function findById(int $moduleId): ?ModuleEntity;
    public function findByCourseId(int $courseId): array;
    public function findByIdAndCourseId(int $moduleId, int $courseId): ?ModuleEntity;
    public function updateModuleStatus(int $moduleId, int $status): void;
    public function createAtEnd(
        int $courseId,
        string $name
    ): ModuleEntity;
    public function belongsToUser(
        int $moduleId,
        int $userId
    ): bool;

    public function updateName(
        int $moduleId,
        string $name
    ): ModuleEntity;
}
