<?php

namespace Promolider\Domain\Marketing\Entities;

class Tool
{
    public function __construct(
        public ?int $id,
        public string $type,
        public string $title,
        public ?string $description,
        public ?string $status,
        public ?int $categoryId,
        public ?int $producerId,
        public ?string $image,
        public ?\DateTime $createdAt,
        public ?\DateTime $updatedAt,
    ) {}

    public function isActive(): bool
    {
        return $this->status === 'active' || $this->status === '1';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft' || $this->status === '0';
    }
}
