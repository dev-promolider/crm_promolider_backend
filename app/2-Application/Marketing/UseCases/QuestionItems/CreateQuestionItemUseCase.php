<?php

namespace Promolider\Application\Marketing\UseCases\QuestionItems;

use Promolider\Domain\Marketing\Ports\Out\QuestionCategoryRepositoryInterface;

class CreateQuestionItemUseCase
{
    public function __construct(
        private QuestionCategoryRepositoryInterface $repository,
    ) {}

    /**
     * @param int $categoryId
     * @param array $data  Expected keys: title, body?, status, difficulty, time_limit?, is_active?, options[]
     *                     options: [{text, is_correct?}, ...]
     * @param int $userId
     * @return array
     */
    public function execute(int $categoryId, array $data, int $userId): array
    {
        $questionData = [
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'difficulty' => $data['difficulty'] ?? 'medium',
            'time_limit' => $data['time_limit'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'created_by' => $userId,
            'updated_by' => $userId,
        ];

        // Published questions must always be active
        if ($data['status'] === 'published') {
            $questionData['is_active'] = true;
        }

        $question = $this->repository->createQuestion($categoryId, $questionData);

        // Create options
        $optionsData = $data['options'] ?? [];
        foreach ($optionsData as $index => $optData) {
            $label = chr(65 + $index); // A, B, C, D...
            $option = [
                'question_item_id' => $question->getId(),
                'label' => $label,
                'text' => $optData['text'],
                'is_correct' => $optData['is_correct'] ?? false,
                'position' => $index,
            ];
            // Store option via the repository
            $this->repository->createOption($option);
        }

        $this->repository->incrementQuestionsCount($categoryId);

        return $this->repository->findQuestionById($question->getId())->toArray();
    }
}
