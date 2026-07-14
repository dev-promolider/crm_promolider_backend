<?php

namespace Promolider\Application\Marketing\UseCases\QuestionItems;

use Promolider\Domain\Marketing\Ports\Out\QuestionCategoryRepositoryInterface;

class DeleteQuestionItemUseCase
{
    public function __construct(
        private QuestionCategoryRepositoryInterface $repository,
    ) {}

    public function execute(int $id): bool
    {
        // Find question first to get category_id for decrement
        $question = $this->repository->findQuestionById($id);
        if (!$question) {
            return false;
        }

        $categoryId = $question->getQuestionCategoryId();

        if (!$this->repository->deleteQuestion($id)) {
            return false;
        }

        $this->repository->decrementQuestionsCount($categoryId);
        return true;
    }
}
