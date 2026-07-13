<?php

namespace Promolider\Domain\Marketing\Entities;

class Meeting
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $userId,
        public readonly string $title,
        public readonly ?string $description,
        public readonly \DateTime $startDate,
        public readonly ?\DateTime $endDate,
        public readonly ?string $link,
        public readonly ?string $type,
        public readonly ?\DateTime $createdAt,
    ) {}
}
