<?php

namespace Promolider\Application\Marketing\UseCases\QuestionItems;

use Promolider\Domain\Marketing\Ports\Out\QuestionCategoryRepositoryInterface;

class UpdateQuestionItemUseCase
{
    public function __construct(
        private QuestionCategoryRepositoryInterface $repository,
    ) {}

    /**
     * @param int $id
     * @param array $data  Expected keys: title?, body?, status?, difficulty?, time_limit?, is_active?, options[]
     * @param int $userId
     * @return array|null
     */
    public function execute(int $id, array $data, int $userId): ?array
    {
                $questionData = [
            'title' => $data['title'] ?? null,
            'body' => $data['body'] ?? null,
            'status' => $data['status'] ?? null,
            'difficulty' => $data['difficulty'] ?? null,
            'time_limit' => $data['time_limit'] ?? null,
            'updated_by' => $userId,
        ];

        // If status is 'published' and is_active is not explicitly sent, auto-activate
        $explicitIsActive = array_key_exists('is_active', $data);
        if ($explicitIsActive) {
            $questionData['is_active'] = $data['is_active'];
        } elseif (($data['status'] ?? null) === 'published') {
            $questionData['is_active'] = true;
        }

        // Remove nulls to avoid overwriting with null
        $questionData = array_filter($questionData, fn($v) => $v !== null);

        $optionsData = $data['options'] ?? [];
        $formattedOptions = [];
        foreach ($optionsData as $index => $optData) {
            $label = chr(65 + $index);
            $formattedOptions[] = [
                'label' => $label,
                'text' => $optData['text'],
                'is_correct' => $optData['is_correct'] ?? false,
                'position' => $index,
            ];
        }

        $question = $this->repository->updateQuestion($id, $questionData, $formattedOptions);
        return $question?->toArray();
    }
}
