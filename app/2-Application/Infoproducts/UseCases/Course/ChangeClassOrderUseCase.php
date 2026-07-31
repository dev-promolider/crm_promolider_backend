<?php

namespace Promolider\Application\Infoproducts\UseCases\Course;

use Illuminate\Auth\Access\AuthorizationException;
use Promolider\Application\Infoproducts\Exceptions\InfoproductNotFoundException;
use Promolider\Domain\Infoproducts\Ports\Out\ModuleClassRepositoryInterface;
use RuntimeException;

final class ChangeClassOrderUseCase
{
    public function __construct(
        private ModuleClassRepositoryInterface $repository
    ) {
    }

    public function execute(
        int $userId,
        int $courseId,
        array $items
    ): array {
        $ownerId = $this->repository->findCourseOwnerId($courseId);

        if ($ownerId === null) {
            throw new InfoproductNotFoundException();
        }

        if ($ownerId !== $userId) {
            throw new AuthorizationException(
                'No tienes autorización para ordenar las clases de este curso.'
            );
        }

        $classIds = array_map(
            static fn (array $item): int => (int) $item['id'],
            $items
        );

        if (!$this->repository->classesBelongToCourse(
            $classIds,
            $courseId
        )) {
            throw new RuntimeException(
                'Una o más clases no pertenecen al curso indicado.'
            );
        }

        $newOrder = [];

        foreach (array_values($items) as $index => $item) {
            $newOrder[] = [
                'id' => (int) $item['id'],
                'order' => $index + 1,
            ];
        }

        $this->repository->updateClassesOrder($newOrder);

        return $newOrder;
    }
}
