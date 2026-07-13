<?php

namespace Promolider\Domain\Marketing\Entities;

class Reward
{
    public function __construct(
        private readonly ?int $id,
        private readonly string $name,
        private readonly string $description,
        private readonly float $cost,
        private readonly ?int $stock,
        private readonly string $image,
        private readonly bool $active,
        private readonly ?int $redemptionCount = null,
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getDescription(): string { return $this->description; }
    public function getCost(): float { return $this->cost; }
    public function getStock(): ?int { return $this->stock; }
    public function getImage(): string { return $this->image; }
    public function isActive(): bool { return $this->active; }
    public function getRedemptionCount(): ?int { return $this->redemptionCount; }

    public function hasStock(): bool
    {
        return is_null($this->stock) || $this->stock > 0;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'cost' => $this->cost,
            'stock' => $this->stock,
            'image' => $this->image,
            'active' => $this->active,
            'redemption_count' => $this->redemptionCount,
            'has_stock' => $this->hasStock(),
        ];
    }
}
