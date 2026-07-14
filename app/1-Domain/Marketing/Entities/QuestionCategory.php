<?php

namespace Promolider\Domain\Marketing\Entities;

class QuestionCategory
{
    public function __construct(
        private ?int $id,
        private string $name,
        private ?string $slug,
        private ?string $description,
        private bool $isActive,
        private int $questionsCount,
        private ?int $createdBy,
        private ?int $updatedBy,
        private ?string $createdAt,
        private ?string $updatedAt,
        private array $questions = [],
    ) {}

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function getQuestionsCount(): int
    {
        return $this->questionsCount;
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

    public function getQuestions(): array
    {
        return $this->questions;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_active' => $this->isActive,
            'questions_count' => $this->questionsCount,
            'created_by' => $this->createdBy,
            'updated_by' => $this->updatedBy,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'questions' => array_map(fn($q) => $q instanceof QuestionItem ? $q->toArray() : $q, $this->questions),
        ];
    }
}
