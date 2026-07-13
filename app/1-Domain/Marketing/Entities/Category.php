<?php

namespace Promolider\Domain\Marketing\Entities;

class Category
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $type,
        public readonly ?string $icon,
        public readonly ?int $parentId,
        public readonly ?\DateTime $createdAt,
    ) {}
}
