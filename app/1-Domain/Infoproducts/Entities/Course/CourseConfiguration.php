<?php

namespace Promolider\Domain\Infoproducts\Entities\Course;

use JsonSerializable;

class CourseConfiguration implements JsonSerializable
{
    public function __construct(
        private int $id,
        private int $course_id,
        private string $data,
        private int $condition_to_certificate,
        private int $type_certificate,
        private string $validated_by,
        private int $customized_certificate
    ){}

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'data' => $this->data,
            'condition_to_certificate' => $this->condition_to_certificate,
            'type_certificate' => $this->type_certificate,
            'validated_by' => $this->validated_by,
            'customized_certificate' => $this->customized_certificate,
        ];
    }
}
