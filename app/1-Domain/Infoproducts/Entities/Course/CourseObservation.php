<?php

namespace Promolider\Domain\Infoproducts\Entities\Course;

use JsonSerializable;

final class CourseObservation implements JsonSerializable
{
    public function __construct(
        private int $id,
        private int $analystId,
        private int $producerId,
        private int $classId,
        private int $courseId,
        private string $description,
        private string $status,
        private ?string $createdAt = null,
        private ?string $updatedAt = null
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getAnalystId(): int
    {
        return $this->analystId;
    }

    public function getProducerId(): int
    {
        return $this->producerId;
    }

    public function getClassId(): int
    {
        return $this->classId;
    }

    public function getCourseId(): int
    {
        return $this->courseId;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function isActive(): bool
    {
        return $this->status === '1';
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'id_analyst' => $this->analystId,
            'id_productor' => $this->producerId,
            'id_class' => $this->classId,
            'id_course' => $this->courseId,
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
