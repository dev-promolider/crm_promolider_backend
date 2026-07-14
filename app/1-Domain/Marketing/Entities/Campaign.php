<?php

namespace Promolider\Domain\Marketing\Entities;

class Campaign
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $type,
        public ?string $description,
        public int $status,
        public ?int $producerId,
        public ?string $image,
        public ?\DateTime $startDate,
        public ?\DateTime $endDate,
        public ?\DateTime $createdAt,
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
