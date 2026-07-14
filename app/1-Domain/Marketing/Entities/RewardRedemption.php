<?php

namespace Promolider\Domain\Marketing\Entities;

class RewardRedemption
{
    public function __construct(
        private ?int $id,
        private int $userId,
        private int $rewardId,
        private float $cost,
        private string $status,
        private ?string $notes,
        private ?string $processedAt,
        private ?int $processedBy,
        private ?string $userName = null,
        private ?string $rewardName = null,
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getRewardId(): int { return $this->rewardId; }
    public function getCost(): float { return $this->cost; }
    public function getStatus(): string { return $this->status; }
    public function getNotes(): ?string { return $this->notes; }
    public function getProcessedAt(): ?string { return $this->processedAt; }
    public function getProcessedBy(): ?int { return $this->processedBy; }
    public function getUserName(): ?string { return $this->userName; }
    public function getRewardName(): ?string { return $this->rewardName; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'reward_id' => $this->rewardId,
            'cost' => $this->cost,
            'status' => $this->status,
            'notes' => $this->notes,
            'processed_at' => $this->processedAt,
            'processed_by' => $this->processedBy,
            'user_name' => $this->userName,
            'reward_name' => $this->rewardName,
        ];
    }
}
