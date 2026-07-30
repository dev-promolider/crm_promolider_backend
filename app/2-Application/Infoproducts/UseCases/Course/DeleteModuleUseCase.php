<?php

namespace Promolider\Application\Infoproducts\UseCases\Course;

use Promolider\Domain\Infoproducts\Ports\Out\ModuleRepositoryInterface;

final class DeleteModuleUseCase
{
    public function __construct(
        private ModuleRepositoryInterface $moduleRepository
    ) {
    }

    public function execute(
        int $userId,
        int $moduleId
    ): array {
        $module = $this->moduleRepository->findById($moduleId);

        if ($module === null) {
            throw new \Exception('Módulo no encontrado', 404);
        }

        if (!$this->moduleRepository->belongsToUser(
            $moduleId,
            $userId
        )) {
            throw new \Exception('No tienes permiso para eliminar este módulo', 403);
        }

        $courseId = $module->getCourseId();

        $this->moduleRepository->deleteWithClasses($moduleId);

        return [
            'status' => 'ok',
            'modules' => $this->moduleRepository
                ->findByCourseId($courseId),
        ];
    }
}
