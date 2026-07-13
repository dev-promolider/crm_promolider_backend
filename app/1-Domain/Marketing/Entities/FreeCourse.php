<?php

namespace Promolider\Domain\Marketing\Entities;

class FreeCourse
{
    public function __construct(
        private readonly ?int $id,
        private readonly string $courseName,
        private readonly ?int $categoryId,
        private readonly ?string $categoryName,
        private readonly ?string $description,
        private readonly ?string $status,
        private readonly ?string $createdAt,
        private readonly ?string $updatedAt,
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
