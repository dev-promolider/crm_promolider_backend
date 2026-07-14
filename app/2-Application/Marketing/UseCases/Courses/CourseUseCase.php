<?php

namespace Promolider\Application\Marketing\UseCases\Courses;

use Promolider\Domain\Marketing\Ports\Out\CourseRepositoryInterface;

class CourseUseCase
{
    public function __construct(
        private CourseRepositoryInterface $repository,
    ) {}

    // ==================== COURSES ====================

    public function listCourses(array $filters = []): array
    {
        return $this->repository->listCourses($filters);
    }

    public function getCourse(int $id): ?array
    {
        return $this->repository->getCourseWithModules($id);
    }

    public function createCourse(array $data): array
    {
        return $this->repository->createCourse($data);
    }

    public function updateCourse(int $id, array $data): ?array
    {
        return $this->repository->updateCourse($id, $data);
    }

    public function deleteCourse(int $id): bool
    {
        return $this->repository->deleteCourse($id);
    }

    // ==================== MODULES ====================

    public function listModules(int $courseId): array
    {
        return $this->repository->listModules($courseId);
    }

    public function getModule(int $id): ?array
    {
        return $this->repository->getModule($id);
    }

    public function createModule(array $data): array
    {
        return $this->repository->createModule($data);
    }

    public function updateModule(int $id, array $data): ?array
    {
        return $this->repository->updateModule($id, $data);
    }

    public function deleteModule(int $id): bool
    {
        return $this->repository->deleteModule($id);
    }

    public function reorderModules(int $courseId, array $order): bool
    {
        return $this->repository->reorderModules($courseId, $order);
    }

    // ==================== CLASSES ====================

    public function listClasses(int $moduleId): array
    {
        return $this->repository->listClasses($moduleId);
    }

    public function getClass(int $id): ?array
    {
        return $this->repository->getClass($id);
    }

    public function createClass(array $data): array
    {
        return $this->repository->createClass($data);
    }

    public function updateClass(int $id, array $data): ?array
    {
        return $this->repository->updateClass($id, $data);
    }

    public function deleteClass(int $id): bool
    {
        return $this->repository->deleteClass($id);
    }

    // ==================== PROGRESS ====================

    public function getProgress(int $userId, int $courseId): array
    {
        return $this->repository->syncProgress($userId, $courseId);
    }

    public function completeLesson(int $userId, int $courseId, int $lessonId): bool
    {
        return $this->repository->completeLesson($userId, $courseId, $lessonId);
    }

    public function updateProgress(int $userId, int $courseId, float $progress): bool
    {
        return $this->repository->updateCourseProgress($userId, $courseId, $progress);
    }

    // ==================== RATINGS ====================

    public function listRatings(int $courseId): array
    {
        return $this->repository->listRatings($courseId);
    }

    public function createRating(int $userId, int $courseId, int $points, ?string $commentary): array
    {
        return $this->repository->createRating($userId, $courseId, $points, $commentary);
    }

    // ==================== OBSERVATIONS ====================

    public function createObservation(array $data): array
    {
        return $this->repository->createObservation($data);
    }

    public function listObservations(int $classId): array
    {
        return $this->repository->listObservations($classId);
    }

    // ==================== GAMES ====================

    public function listGames(int $courseId): array
    {
        return $this->repository->listGames($courseId);
    }

    public function getGame(int $id): ?array
    {
        return $this->repository->getGame($id);
    }

    public function createGame(array $data): array
    {
        return $this->repository->createGame($data);
    }

    public function updateGame(int $id, array $data): ?array
    {
        return $this->repository->updateGame($id, $data);
    }

    public function deleteGame(int $id): bool
    {
        return $this->repository->deleteGame($id);
    }

    public function listGameDetails(int $gameId): array
    {
        return $this->repository->listGameDetails($gameId);
    }

    public function createGameDetail(array $data): array
    {
        return $this->repository->createGameDetail($data);
    }

    public function deleteGameDetail(int $id): bool
    {
        return $this->repository->deleteGameDetail($id);
    }

    // ==================== CERTIFICATES ====================

    public function listCertificates(int $userId): array
    {
        return $this->repository->listCertificates($userId);
    }

    public function createCertificate(array $data): array
    {
        return $this->repository->createCertificate($data);
    }

    public function listTemplates(): array
    {
        return $this->repository->listTemplates();
    }

    public function createTemplate(array $data): array
    {
        return $this->repository->createTemplate($data);
    }

    public function updateTemplate(int $id, array $data): ?array
    {
        return $this->repository->updateTemplate($id, $data);
    }
}
