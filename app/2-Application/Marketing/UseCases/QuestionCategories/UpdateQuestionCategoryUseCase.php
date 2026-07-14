<?php

namespace Promolider\Application\Marketing\UseCases\QuestionCategories;

use Promolider\Domain\Marketing\Ports\Out\QuestionCategoryRepositoryInterface;

class UpdateQuestionCategoryUseCase
{
    public function __construct(
        private QuestionCategoryRepositoryInterface $repository,
    ) {}

    public function execute(int $id, array $data): ?array
    {
        $category = $this->repository->update($id, $data);
        return $category->toArray();
    }
}
