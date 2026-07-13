<?php

namespace Promolider\Application\Marketing\UseCases\QuestionCategories;

use Promolider\Domain\Marketing\Ports\Out\QuestionCategoryRepositoryInterface;

class DeleteQuestionCategoryUseCase
{
    public function __construct(
        private readonly QuestionCategoryRepositoryInterface $repository,
    ) {}

    public function execute(int $id): bool
    {
        return $this->repository->delete($id);
    }
}
