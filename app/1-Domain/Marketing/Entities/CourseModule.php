<?php

namespace Promolider\Domain\Marketing\Entities;

class CourseModule
{
    public function __construct(
        private readonly ?int $id,
        private readonly int $courseId,
        private readonly string $name,
        private readonly int $order,
        private readonly int $status,
        private readonly array $classes = [],
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
