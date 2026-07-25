<?php

namespace Promolider\Domain\Infoproducts\Entities\Course;

use JsonSerializable;

class Module implements JsonSerializable
{
    public function __construct(
        private int $id,
        private int $courseId,
        private string $name,
        private ?string $description,
        private int $order,
        private int $status
    ){}

     public function getId(): int
    {
        return $this->id;
    }

    public function getCourseId(): int
    {
        return $this->courseId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'id_courses' => $this->courseId,
            'name' => $this->name,
            'description' => $this->description,
            'order' => $this->order,
            'status' => $this->status
        ];
    }
}
