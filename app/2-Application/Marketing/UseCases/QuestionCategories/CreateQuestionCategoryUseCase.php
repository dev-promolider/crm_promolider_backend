<?php

namespace Promolider\Application\Marketing\UseCases\QuestionCategories;

use Promolider\Domain\Marketing\Ports\Out\QuestionCategoryRepositoryInterface;

class CreateQuestionCategoryUseCase
{
    public function __construct(
        private QuestionCategoryRepositoryInterface $repository,
    ) {}

    public function execute(array $data): array
    {
        $category = $this->repository->create($data);
        return $category->toArray();
    }
}
