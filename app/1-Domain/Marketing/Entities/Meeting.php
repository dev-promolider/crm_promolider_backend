<?php

namespace Promolider\Domain\Marketing\Entities;

class Meeting
{
    public function __construct(
        public ?int $id,
        public int $userId,
        public string $title,
        public ?string $description,
        public \DateTime $startDate,
        public ?\DateTime $endDate,
        public ?string $link,
        public ?string $type,
        public ?\DateTime $createdAt,
    ) {}
}
