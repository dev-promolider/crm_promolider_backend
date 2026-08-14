<?php

namespace Promolider\Domain\Marketing\Ports\Out;

use Promolider\Domain\Marketing\Entities\Tool;
use Promolider\Domain\Marketing\Entities\Campaign;
use Promolider\Domain\Marketing\Entities\Category;

interface ToolRepositoryInterface
{
    /** @return Tool[] */
    public function getToolsByUser(int $userId, ?int $courseId = null): array;

    /** @return Campaign[] */
    public function getCampaigns(): array;

    /** @return array{masterclasses: array, ebooks: array, mini_courses: array} */
    public function getUserCampaigns(int $userId): array;

    /** @return Campaign[] */
    public function getCampaignsByType(string $type): array;

    /** @return Category[] */
    public function getCategories(?string $type = null): array;

    public function createCategory(array $data): Category;

    /** @return array{masterclasses: array, ebooks: array, miniCourses: array} */
    public function getToolsWithStatus(int $userId): array;

    public function verifyToolOwnership(string $type, int $toolId, int $userId): bool;

    public function updateToolStatus(string $type, int $toolId, string $status): bool;

    public function deleteTool(string $type, int $toolId): bool;

    public function storeTool(string $type, array $data): int;

    /** Obtener una herramienta por ID con sus relaciones (imágenes, documentos, etc.) */
    public function getToolById(string $type, int $toolId): ?array;

    /** Actualizar datos de una herramienta */
    public function updateTool(string $type, int $toolId, array $data): bool;
}
