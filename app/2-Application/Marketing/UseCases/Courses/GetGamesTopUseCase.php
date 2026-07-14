<?php

namespace Promolider\Application\Marketing\UseCases\Courses;

use Promolider\Domain\Marketing\Ports\Out\CourseRepositoryInterface;

class GetGamesTopUseCase
{
    public function __construct(
        private CourseRepositoryInterface $courseRepository,
    ) {}

    public function execute(int $courseId, int $userId): array
    {
        return $this->courseRepository->getGamesTop($courseId, $userId);
    }
}
