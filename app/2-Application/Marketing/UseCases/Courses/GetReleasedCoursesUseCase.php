<?php

namespace Promolider\Application\Marketing\UseCases\Courses;

use Promolider\Domain\Marketing\Ports\Out\CourseRepositoryInterface;

class GetReleasedCoursesUseCase
{
    public function __construct(
        private CourseRepositoryInterface $courseRepository,
    ) {}

    public function execute(int $userId, int $limit = 10): array
    {
        return $this->courseRepository->getReleasedCourses($userId, $limit);
    }
}
