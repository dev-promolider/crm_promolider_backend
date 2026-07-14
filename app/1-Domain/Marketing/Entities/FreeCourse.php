<?php

namespace Promolider\Domain\Marketing\Entities;

class FreeCourse
{
    public function __construct(
        private ?int $id,
        private string $courseName,
        private ?int $categoryId,
        private ?string $categoryName,
        private ?string $description,
        private ?string $status,
        private ?string $createdAt,
        private ?string $updatedAt,
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getCourseName(): string { return $this->courseName; }
    public function getCategoryId(): ?int { return $this->categoryId; }
    public function getCategoryName(): ?string { return $this->categoryName; }
    public function getDescription(): ?string { return $this->description; }
    public function getStatus(): ?string { return $this->status; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'course_name' => $this->courseName,
            'category_id' => $this->categoryId,
            'category_name' => $this->categoryName,
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
