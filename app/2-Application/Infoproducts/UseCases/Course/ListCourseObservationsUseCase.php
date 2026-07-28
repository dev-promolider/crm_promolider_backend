<?php

namespace Promolider\Application\Infoproducts\UseCases\Course;

use Promolider\Application\Infoproducts\Exceptions\InfoproductNotFoundException;
use Promolider\Application\Infoproducts\Exceptions\InfoproductNotOwnedException;
use Promolider\Domain\Infoproducts\Ports\Out\CourseRepositoryInterface;

final class ListCourseObservationsUseCase
{
    public function __construct(
        private CourseRepositoryInterface $courseRepository
    ) {
    }

    public function execute(
        int $userId,
        int $courseId
    ): array {
        $courseOwnerId = $this->courseRepository
            ->findOwnerId($courseId);

        if ($courseOwnerId === null) {
            throw new \Exception('Course not found.', 404);
        }

        if ($courseOwnerId !== $userId) {
            throw new \Exception('You do not have permission to access this course observations.', 403);
        }

        return $this->courseRepository
            ->findActiveObservations($courseId);
    }
}
