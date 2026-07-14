<?php

namespace Promolider\Application\Marketing\UseCases\QuestionCategories;

use Promolider\Domain\Marketing\Ports\Out\QuestionCategoryRepositoryInterface;

class ListQuestionCategoriesUseCase
{
    public function __construct(
        private readonly QuestionCategoryRepositoryInterface $repository,
    ) {}

    public function execute(array $filters = []): array
    {
        return $this->repository->list($filters);
    }
}
