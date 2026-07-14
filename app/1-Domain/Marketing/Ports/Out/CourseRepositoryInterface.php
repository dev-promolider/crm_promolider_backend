<?php

namespace Promolider\Domain\Marketing\Ports\Out;

interface CourseRepositoryInterface
{
    // === Courses ===
    public function listCourses(array $filters = []): array;
    public function getCourse(int $id): ?array;
    public function createCourse(array $data): array;
    public function updateCourse(int $id, array $data): ?array;
    public function deleteCourse(int $id): bool;
    public function getCourseWithModules(int $id): ?array;

    // === Modules ===
    public function listModules(int $courseId): array;
    public function getModule(int $id): ?array;
    public function createModule(array $data): array;
    public function updateModule(int $id, array $data): ?array;
    public function deleteModule(int $id): bool;
    public function reorderModules(int $courseId, array $order): bool;

    // === Classes ===
    public function listClasses(int $moduleId): array;
    public function getClass(int $id): ?array;
    public function createClass(array $data): array;
    public function updateClass(int $id, array $data): ?array;
    public function deleteClass(int $id): bool;

    // === Progress ===
    public function getCompletedLessons(int $userId, int $courseId): array;
    public function completeLesson(int $userId, int $courseId, int $lessonId): bool;
    public function getCourseProgress(int $userId, int $courseId): float;
    public function updateCourseProgress(int $userId, int $courseId, float $progress): bool;
    public function getTotalLessons(int $courseId): int;
    public function syncProgress(int $userId, int $courseId): array;

    // === Ratings ===
    public function listRatings(int $courseId): array;
    public function createRating(int $userId, int $courseId, int $points, ?string $commentary): array;

    // === Observations ===
    public function createObservation(array $data): array;
    public function listObservations(int $classId): array;

    // === Games ===
    public function listGames(int $courseId): array;
    public function getGame(int $id): ?array;
    public function createGame(array $data): array;
    public function updateGame(int $id, array $data): ?array;
    public function deleteGame(int $id): bool;
    public function listGameDetails(int $gameId): array;
    public function createGameDetail(array $data): array;
    public function deleteGameDetail(int $id): bool;

    // === Search ===
    public function searchCourses(string $query, ?int $userId = null, array $filters = []): array;

    // === Course Expiration ===
    public function getCourseExpiration(int $courseId, int $userId): ?array;

    // === Related Courses ===
    public function getRelatedCourses(int $courseId, int $limit = 5, ?int $excludeUserId = null): array;

    // === Released Courses ===
    public function getReleasedCourses(int $userId, int $limit = 10): array;

    // === Last Played Courses ===
    public function getLastPlayedCourses(int $userId, int $limit = 5): array;

    // === Game Leaderboard ===
    public function getGamesTop(int $courseId, int $userId): array;

    // === Certificates ===
    public function listCertificates(int $userId): array;
    public function createCertificate(array $data): array;
    public function getCertificate(int $id): ?array;
    public function listTemplates(): array;
    public function createTemplate(array $data): array;
    public function updateTemplate(int $id, array $data): ?array;
}
