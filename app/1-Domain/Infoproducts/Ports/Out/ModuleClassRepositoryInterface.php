<?php

namespace Promolider\Domain\Infoproducts\Ports\Out;

use Promolider\Domain\Infoproducts\Entities\Course\Clas as ClassEntity;

interface ModuleClassRepositoryInterface
{
    public function transaction(callable $callback);
    public function findClassesByModuleId(int $moduleId): array;
    public function createClass(array $data): ClassEntity;
    public function updateClass(
        int $classId,
        array $data
    ): void;
    public function updateModuleStatus(
        int $moduleId,
        int $status
    ): void;
    public function updateCourseStatus(
        int $courseId,
        int $status
    ): void;
    public function findClassContext(int $classId): ?array;
    public function findClassDetails(int $classId): ?array;
    public function saveVideoInformation(
        int $classId,
        string $filename,
        string $path
    ): void;
    public function deleteClassWithRelations(int $classId): void;
}
