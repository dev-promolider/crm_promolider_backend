<?php

namespace Promolider\Application\Marketing\UseCases\QuestionCategories;

use Promolider\Domain\Marketing\Ports\Out\QuestionCategoryRepositoryInterface;

class ToggleQuestionCategoryStatusUseCase
{
    public function __construct(
        private readonly QuestionCategoryRepositoryInterface $repository,
    ) {}

    public function execute(int $id): ?array
    {
        $category = $this->repository->toggleStatus($id);
        return $category->toArray();
    }
}
