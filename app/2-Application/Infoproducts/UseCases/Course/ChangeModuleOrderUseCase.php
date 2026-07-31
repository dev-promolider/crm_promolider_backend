<?php

namespace Promolider\Application\Infoproducts\UseCases\Course;

use Illuminate\Auth\Access\AuthorizationException;
use Promolider\Application\Infoproducts\Exceptions\InfoproductNotFoundException;
use Promolider\Domain\Infoproducts\Ports\Out\ModuleRepositoryInterface;
use Promolider\Domain\Infoproducts\Ports\Out\CourseRepositoryInterface;
use RuntimeException;

final class ChangeModuleOrderUseCase
{
    public function __construct(
        private ModuleRepositoryInterface $moduleRepository,
        private CourseRepositoryInterface $courseRepository
    ) {
    }

    public function execute(
        int $userId,
        int $courseId,
        array $items
    ): array {
        $courseOwnerId = $this->courseRepository
            ->findOwnerId($courseId);

        if ($courseOwnerId === null) {
            throw new \Exception(
                'El curso indicado no existe o no tiene un propietario asignado.'
            );
        }

        if ($courseOwnerId !== $userId) {
            throw new AuthorizationException(
                'No tienes autorización para ordenar los módulos de este curso.'
            );
        }

        $moduleIds = array_map(
            static fn (array $item): int => (int) $item['id'],
            $items
        );

        if (!$this->moduleRepository->modulesBelongToCourse(
            $moduleIds,
            $courseId
        )) {
            throw new RuntimeException(
                'Uno o más módulos no pertenecen al curso indicado.'
            );
        }

        $newOrder = [];

        foreach (array_values($items) as $index => $item) {
            $newOrder[] = [
                'id' => (int) $item['id'],
                'order' => $index + 1,
            ];
        }

        $this->moduleRepository->updateModulesOrder($newOrder);

        return $newOrder;
    }
}
