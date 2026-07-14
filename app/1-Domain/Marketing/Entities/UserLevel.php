<?php

namespace Promolider\Domain\Marketing\Entities;

class UserLevel
{
    public function __construct(
        private ?int $id,
        private string $description,
        private int $experienceRequired,
        private ?string $urlIcon,
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getDescription(): string { return $this->description; }
    public function getExperienceRequired(): int { return $this->experienceRequired; }
    public function getUrlIcon(): ?string { return $this->urlIcon; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'experience_required' => $this->experienceRequired,
            'url_icon' => $this->urlIcon,
        ];
    }
}
