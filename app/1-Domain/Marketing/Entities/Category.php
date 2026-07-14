<?php

namespace Promolider\Domain\Marketing\Entities;

class Category
{
    public function __construct(
        public ?int $id,
        public string $name,
        public ?string $description,
        public ?string $type,
        public ?string $icon,
        public ?int $parentId,
        public ?\DateTime $createdAt,
    ) {}
}
