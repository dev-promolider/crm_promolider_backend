<?php

namespace Promolider\Application\Infoproducts\UseCases\Course;

use Promolider\Domain\Infoproducts\Entities\Course\Module as ModuleEntity;
use Promolider\Domain\Infoproducts\Ports\Out\ModuleRepositoryInterface;

final class UpdateModuleUseCase
{
    public function __construct(
        private ModuleRepositoryInterface $moduleRepository
    ) {
    }

    public function execute(
        int $userId,
        int $moduleId,
        string $name
    ): ModuleEntity {
        $module = $this->moduleRepository->findById($moduleId);

        if ($module === null) {
            throw new \Exception('El módulo no existe.');
        }

        if (!$this->moduleRepository->belongsToUser(
            $moduleId,
            $userId
        )) {
            throw new \Exception('El módulo no pertenece al usuario.');
        }

        return $this->moduleRepository->updateName(
            $moduleId,
            trim($name)
        );
    }
}
