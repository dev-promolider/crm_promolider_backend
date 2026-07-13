<?php

namespace Promolider\Domain\Marketing\Entities;

class Tool
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $type,
        public readonly string $title,
        public readonly ?string $description,
        public readonly ?string $status,
        public readonly ?int $categoryId,
        public readonly ?int $producerId,
        public readonly ?string $image,
        public readonly ?\DateTime $createdAt,
        public readonly ?\DateTime $updatedAt,
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
