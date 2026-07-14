<?php

namespace Promolider\Domain\Marketing\Entities;

class QuestionItem
{
    public function __construct(
        private readonly ?int $id,
        private readonly int $questionCategoryId,
        private readonly string $title,
        private readonly ?string $body,
        private readonly string $status,
        private readonly string $difficulty,
        private readonly ?int $timeLimit,
        private readonly bool $isActive,
        private readonly ?array $meta,
        private readonly ?int $createdBy,
        private readonly ?int $updatedBy,
        private readonly ?string $createdAt,
        private readonly ?string $updatedAt,
        private readonly array $options = [],
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getQuestionCategoryId(): int
    {
        return $this->questionCategoryId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getDifficulty(): string
    {
        return $this->difficulty;
    }

    public function getTimeLimit(): ?int
    {
        return $this->timeLimit;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getMeta(): ?array
    {
        return $this->meta;
    }

    public function getCreatedBy(): ?int
    {
        return $this->createdBy;
    }

    public function getUpdatedBy(): ?int
    {
        return $this->updatedBy;
    }

    public function getCreatedAt(): ?string
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?string
    {
        return $this->updatedAt;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'question_category_id' => $this->questionCategoryId,
            'title' => $this->title,
            'body' => $this->body,
            'status' => $this->status,
            'difficulty' => $this->difficulty,
            'time_limit' => $this->timeLimit,
            'is_active' => $this->isActive,
            'meta' => $this->meta,
            'created_by' => $this->createdBy,
            'updated_by' => $this->updatedBy,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'options' => array_map(fn($o) => $o instanceof QuestionItemOption ? $o->toArray() : $o, $this->options),
        ];
    }
}
