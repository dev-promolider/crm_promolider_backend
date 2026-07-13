<?php

namespace Promolider\Domain\Marketing\Entities;

class UserClassroomPoint
{
    public function __construct(
        private readonly ?int $id,
        private readonly int $userId,
        private readonly int $totalPoints,
        private readonly ?string $userName,
        private readonly ?string $userPhoto,
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getTotalPoints(): int { return $this->totalPoints; }
    public function getUserName(): ?string { return $this->userName; }
    public function getUserPhoto(): ?string { return $this->userPhoto; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->userId,
            'total_points' => $this->totalPoints,
            'user_name' => $this->userName,
            'user_photo' => $this->userPhoto,
        ];
    }
}
