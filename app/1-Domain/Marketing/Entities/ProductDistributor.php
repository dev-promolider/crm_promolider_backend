<?php

namespace Promolider\Domain\Marketing\Entities;

class ProductDistributor
{
    public function __construct(
        private ?int $id,
        private int $userId,
        private int $productId,
        private string $productType,
        private string $code,
        private ?string $expiresAt,
        private ?string $invitationLink,
        private bool $exists,
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getProductId(): int { return $this->productId; }
    public function getProductType(): string { return $this->productType; }
    public function getCode(): string { return $this->code; }
    public function getExpiresAt(): ?string { return $this->expiresAt; }
    public function getInvitationLink(): ?string { return $this->invitationLink; }
    public function exists(): bool { return $this->exists; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'product_id' => $this->productId,
            'product_type' => $this->productType,
            'code' => $this->code,
            'expires_at' => $this->expiresAt,
            'invitation_link' => $this->invitationLink,
            'exists' => $this->exists,
        ];
    }
}
