<?php

namespace Promolider\Application\Infoproducts\UseCases\Course;

use Promolider\Domain\Infoproducts\Ports\Out\CourseRepositoryInterface;

class GetCourseConfigurationDataUseCase
{
    public function __construct(
        private CourseRepositoryInterface $courseRepository
    ) {}

    public function execute(int $courseId): array
    {
        $courseConfigurationData = $this->courseRepository->getCourseConfigurationData($courseId);

        if (!$courseConfigurationData) {
            return [
                'success' => false,
                'message' => 'Failed to retrieve course configuration data.'
            ];
        }

        return [
            'success' => true,
            'data' => $courseConfigurationData,
            'message' => 'Course configuration retrieved successfully.'
        ];
    }
}
