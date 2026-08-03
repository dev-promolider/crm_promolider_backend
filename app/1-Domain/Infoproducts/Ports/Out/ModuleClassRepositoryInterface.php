<?php

namespace Promolider\Domain\Infoproducts\Ports\Out;

interface ModuleClassRepositoryInterface
{
    public function findClassesByModuleId(int $moduleId): array;
}
