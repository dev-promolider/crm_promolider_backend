<?php

namespace Promolider\Domain\Marketing\Entities;

class CourseClass
{
    public function __construct(
        private readonly ?int $id,
        private readonly int $courseId,
        private readonly int $moduleId,
        private readonly string $name,
        private readonly ?string $description,
        private readonly ?string $video,
        private readonly ?string $pathUrl,
        private readonly ?int $time,
        private readonly int $order,
        private readonly int $status,
        private readonly ?string $resource,
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getCourseId(): int { return $this->courseId; }
    public function getModuleId(): int { return $this->moduleId; }
    public function getName(): string { return $this->name; }
    public function getDescription(): ?string { return $this->description; }
    public function getVideo(): ?string { return $this->video; }
    public function getPathUrl(): ?string { return $this->pathUrl; }
    public function getTime(): ?int { return $this->time; }
    public function getOrder(): int { return $this->order; }
    public function getStatus(): int { return $this->status; }
    public function getResource(): ?string { return $this->resource; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->courseId,
            'module_id' => $this->moduleId,
            'name' => $this->name,
            'description' => $this->description,
            'video' => $this->video,
            'path_url' => $this->pathUrl,
            'time' => $this->time,
            'order' => $this->order,
            'status' => $this->status,
            'resource' => $this->resource,
        ];
    }
}
