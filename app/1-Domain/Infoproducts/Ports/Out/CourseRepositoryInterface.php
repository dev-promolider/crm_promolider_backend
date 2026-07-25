<?php

namespace Promolider\Domain\Infoproducts\Ports\Out;

use Promolider\Domain\Infoproducts\Entities\Course\CourseConfiguration as CourseConfigurationEntity;

interface CourseRepositoryInterface
{
    public function findModulesById(int $courseId): array;
    public function getOrdersById(int $courseId): array;
    public function storeCourseConfiguration(array $data): ?CourseConfigurationEntity;
    public function getCourseConfigurationData(int $courseId): ?CourseConfigurationEntity;
}
