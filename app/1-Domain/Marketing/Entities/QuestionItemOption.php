<?php

namespace Promolider\Domain\Marketing\Entities;

class QuestionItemOption
{
    public function __construct(
        private readonly ?int $id,
        private readonly int $questionItemId,
        private readonly ?string $label,
        private readonly string $text,
        private readonly bool $isCorrect,
        private readonly int $position,
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestionItemId(): int
    {
        return $this->questionItemId;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function isCorrect(): bool
    {
        return $this->isCorrect;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'question_item_id' => $this->questionItemId,
            'label' => $this->label,
            'text' => $this->text,
            'is_correct' => $this->isCorrect,
            'position' => $this->position,
        ];
    }
}
