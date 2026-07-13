<?php

namespace Promolider\Application\Marketing\UseCases\QuestionCategories;

use Promolider\Domain\Marketing\Ports\Out\QuestionCategoryRepositoryInterface;

class GetQuestionCategoryUseCase
{
    public function __construct(
        private readonly QuestionCategoryRepositoryInterface $repository,
    ) {}

    public function execute(int $id): ?array
    {
        $category = $this->repository->findById($id);
        if (!$category) {
            return null;
        }

        $questions = $this->repository->getQuestionsByCategory($id);

        return array_merge($category->toArray(), ['questions' => $questions]);
    }
}
