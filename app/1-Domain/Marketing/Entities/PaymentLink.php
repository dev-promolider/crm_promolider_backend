<?php

namespace Promolider\Domain\Marketing\Entities;

class PaymentLink
{
    public function __construct(
        private ?int $id,
        private string $name,
        private ?string $slug,
        private ?string $productType,
        private ?int $productId,
        private float $amount,
        private ?string $description,
        private bool $active,
        private int $usageCount,
        private ?string $createdAt,
        private ?string $updatedAt,
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getSlug(): ?string { return $this->slug; }
    public function getProductType(): ?string { return $this->productType; }
    public function getProductId(): ?int { return $this->productId; }
    public function getAmount(): float { return $this->amount; }
    public function getDescription(): ?string { return $this->description; }
    public function isActive(): bool { return $this->active; }
    public function getUsageCount(): int { return $this->usageCount; }
    public function getCreatedAt(): ?string { return $this->createdAt; }
    public function getUpdatedAt(): ?string { return $this->updatedAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'product_type' => $this->productType,
            'product_id' => $this->productId,
            'amount' => $this->amount,
            'description' => $this->description,
            'active' => $this->active,
            'usage_count' => $this->usageCount,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
