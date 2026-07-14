<?php

namespace Promolider\Application\Marketing\UseCases\Courses;

use Promolider\Domain\Marketing\Ports\Out\CourseRepositoryInterface;

class GetLastPlayedCoursesUseCase
{
    public function __construct(
        private CourseRepositoryInterface $courseRepository,
    ) {}

    public function execute(int $userId, int $limit = 5): array
    {
        return $this->courseRepository->getLastPlayedCourses($userId, $limit);
    }
}
