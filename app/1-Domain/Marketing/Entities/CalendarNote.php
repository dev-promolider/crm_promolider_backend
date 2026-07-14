<?php

namespace Promolider\Domain\Marketing\Entities;

class CalendarNote
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly ?string $date,
        public readonly ?string $time,
        public readonly ?string $content,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
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
