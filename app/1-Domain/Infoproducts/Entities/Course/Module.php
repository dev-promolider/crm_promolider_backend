<?php

namespace Promolider\Domain\Infoproducts\Entities\Course;

use JsonSerializable;

class Module implements JsonSerializable
{
    public function __construct(
        private int $id,
        private int $id_courses,
        private string $name,
        private ?string $description,
        private int $order,
        private string $status
    ){}

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'id_courses' => $this->id_courses,
            'name' => $this->name,
            'description' => $this->description,
            'order' => $this->order,
            'status' => $this->status
        ];
    }
}
