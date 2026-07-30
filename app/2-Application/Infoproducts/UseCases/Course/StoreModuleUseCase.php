<?php

namespace Promolider\Application\Infoproducts\UseCases\Course;

use Promolider\Application\Infoproducts\Exceptions\InfoproductNotFoundException;
use Promolider\Application\Infoproducts\Exceptions\InfoproductNotOwnedException;
use Promolider\Domain\Infoproducts\Ports\Out\CourseRepositoryInterface;
use Promolider\Domain\Infoproducts\Ports\Out\ModuleRepositoryInterface;

final class StoreModuleUseCase
{
    public function __construct(
        private ModuleRepositoryInterface $moduleRepository,
        private CourseRepositoryInterface $courseRepository
    ) {
    }

    public function execute(
        int $userId,
        int $courseId,
        string $name
    ): array {
        $courseOwnerId = $this->courseRepository
            ->findOwnerId($courseId);

        if ($courseOwnerId === null) {
            throw new InfoproductNotFoundException();
        }

        if ($courseOwnerId !== $userId) {
            throw new InfoproductNotOwnedException();
        }

        $module = $this->moduleRepository->createAtEnd(
            courseId: $courseId,
            name: trim($name)
        );

        return [
            'status' => 'ok',
            'module' => $module,
            'modules' => $this->moduleRepository
                ->findByCourseId($courseId),
        ];
    }
}
