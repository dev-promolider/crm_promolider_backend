<?php

namespace Promolider\Domain\Infoproducts\Ports\Out;

use Promolider\Domain\Infoproducts\Entities\Course\Clas as ClassEntity;

interface ModuleClassRepositoryInterface
{
    public function transaction(callable $callback);
    public function findClassesByModuleId(int $moduleId): array;
    public function createClass(array $data): ClassEntity;
    public function findClassContext(int $classId): ?array;
    public function saveVideoInformation(
        int $classId,
        string $filename,
        string $path
    ): void;
}
