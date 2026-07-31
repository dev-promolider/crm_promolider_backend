<?php

namespace Promolider\Domain\Infoproducts\Ports\Out;

use Promolider\Domain\Infoproducts\Entities\Course\CourseConfiguration as CourseConfigurationEntity;

interface CourseRepositoryInterface
{
    public function findModulesById(int $courseId): array;
    public function getOrdersById(int $courseId): array;
    public function updateCourseStatus(int $courseId, int $status): void;
    public function findOwnerId(int $courseId): ?int;
    public function findActiveObservations(int $courseId): array;
    public function findAdminIds(): array;
    public function findCourseForReview(int $courseId): ?array;
    public function hasBookFiles(int $courseId): bool;
    public function hasModules(int $courseId): bool;
    public function hasCourseConfiguration(int $courseId): bool;
    public function hasUserConfiguration(
        int $userId,
        int $configurationId
    ): bool;
    public function updateStatus(
        int $courseId,
        int $status
    ): void;
    public function storeCourseConfiguration(array $data): ?CourseConfigurationEntity;
    public function getCourseConfigurationData(int $courseId): ?CourseConfigurationEntity;
    public function transaction(callable $callback);
}
