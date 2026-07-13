<?php

namespace Promolider\Domain\Marketing\Ports\Out;

use Promolider\Domain\Marketing\Entities\QuestionCategory;
use Promolider\Domain\Marketing\Entities\QuestionItem;
use Promolider\Domain\Marketing\Entities\QuestionItemOption;

interface QuestionCategoryRepositoryInterface
{
    // ─── Question Categories ───────────────────────────────────────────────

    public function list(array $filters = []): array;

    public function paginate(array $filters = [], int $perPage = 15): array;

    public function findById(int $id): ?QuestionCategory;

    public function findBySlug(string $slug): ?QuestionCategory;

    public function create(array $data): QuestionCategory;

    public function update(int $id, array $data): QuestionCategory;

    public function toggleStatus(int $id): QuestionCategory;

    public function delete(int $id): bool;

    // ─── Question Items ────────────────────────────────────────────────────

    public function getQuestionsByCategory(int $categoryId, array $filters = []): array;

    public function findQuestionById(int $id): ?QuestionItem;

    public function createQuestion(int $categoryId, array $data): QuestionItem;

    public function createOption(array $data): QuestionItemOption;

    public function updateQuestion(int $id, array $data, array $options): QuestionItem;

    public function deleteQuestion(int $id): bool;

    public function incrementQuestionsCount(int $categoryId): void;

    public function decrementQuestionsCount(int $categoryId): void;
}
