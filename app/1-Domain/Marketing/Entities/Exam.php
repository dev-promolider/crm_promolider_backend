<?php

namespace Promolider\Domain\Marketing\Entities;

class Exam
{
    public function __construct(
        public ?int $id,
        public ?int $courseId,
        public ?int $productorId,
        public ?int $moduleId,
        public ?int $lessonId,
        public string $title,
        public ?int $time,
        public int $maxScore,
        public int $minPassingScore,
        public bool $status,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            courseId: $data['course_id'] ?? null,
            productorId: $data['productor_id'] ?? null,
            moduleId: $data['module_id'] ?? null,
            lessonId: $data['lesson_id'] ?? null,
            title: $data['title'] ?? '',
            time: $data['time'] ?? null,
            maxScore: $data['max_score'] ?? 0,
            minPassingScore: $data['min_passing_score'] ?? 0,
            status: (bool)($data['status'] ?? false),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->courseId,
            'productor_id' => $this->productorId,
            'module_id' => $this->moduleId,
            'lesson_id' => $this->lessonId,
            'title' => $this->title,
            'time' => $this->time,
            'max_score' => $this->maxScore,
            'min_passing_score' => $this->minPassingScore,
            'status' => $this->status,
        ];
    }
}
