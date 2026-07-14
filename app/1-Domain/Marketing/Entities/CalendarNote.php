<?php

namespace Promolider\Domain\Marketing\Entities;

class CalendarNote
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public ?string $date,
        public ?string $time,
        public ?string $content,
        public ?string $createdAt,
        public ?string $updatedAt,
    ) {}

    public function hasTime(): bool
    {
        return !empty($this->time);
    }

    public function hasContent(): bool
    {
        return !empty($this->content);
    }
}
