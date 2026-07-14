<?php

namespace Promolider\Domain\Marketing\Entities;

class CourseModule
{
    public function __construct(
        private ?int $id,
        private int $courseId,
        private string $name,
        private int $order,
        private int $status,
        private array $classes = [],
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getCourseId(): int { return $this->courseId; }
    public function getName(): string { return $this->name; }
    public function getOrder(): int { return $this->order; }
    public function getStatus(): int { return $this->status; }
    public function getClasses(): array { return $this->classes; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->courseId,
            'name' => $this->name,
            'order' => $this->order,
            'status' => $this->status,
            'classes' => $this->classes,
        ];
    }
}
