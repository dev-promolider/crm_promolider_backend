<?php

namespace Promolider\Domain\Marketing\Entities;

class Badge
{
    public function __construct(
        private readonly ?int $id,
        private readonly string $name,
        private readonly string $description,
        private readonly int $level,
        private readonly int $condition,
        private readonly string $icon,
        private readonly bool $obtained = false,
    ) {}

    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getDescription(): string { return $this->description; }
    public function getLevel(): int { return $this->level; }
    public function getCondition(): int { return $this->condition; }
    public function getIcon(): string { return $this->icon; }
    public function isObtained(): bool { return $this->obtained; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'level' => $this->level,
            'condition' => $this->condition,
            'icon' => $this->icon,
            'obtained' => $this->obtained,
        ];
    }
}
