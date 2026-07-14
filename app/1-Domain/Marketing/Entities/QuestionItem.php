<?php

namespace Promolider\Domain\Marketing\Entities;

class QuestionItem
{
    public function __construct(
        private ?int $id,
        private int $questionCategoryId,
        private string $title,
        private ?string $body,
        private string $status,
        private string $difficulty,
        private ?int $timeLimit,
        private bool $isActive,
        private ?array $meta,
        private ?int $createdBy,
        private ?int $updatedBy,
        private ?string $createdAt,
        private ?string $updatedAt,
        private array $options = [],
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
