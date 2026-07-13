<?php

namespace Promolider\Domain\Marketing\Entities;

class Campaign
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly string $type,
        public readonly ?string $description,
        public readonly int $status,
        public readonly ?int $producerId,
        public readonly ?string $image,
        public readonly ?\DateTime $startDate,
        public readonly ?\DateTime $endDate,
        public readonly ?\DateTime $createdAt,
    ) {}

    public function isActive(): bool
    {
        return $this->status === 2;
    }

    public function getCampaignType(): string
    {
        return $this->type;
    }
}
