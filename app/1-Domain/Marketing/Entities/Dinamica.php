<?php

namespace Promolider\Domain\Marketing\Entities;

class Dinamica
{
    public function __construct(
        public ?int $id,
        public ?int $userId,
        public ?int $categoryId,
        public string $nombre,
        public string $tipoDinamica,
        public ?string $descripcion,
        public ?string $slug,
        public bool $isActive = false,
        public bool $isPublic = false,
        public ?int $maxParticipantes = null,
        public ?string $modoInscripcion = null,
        public ?int $tiempoInscripcion = null,
        public ?int $maxGanadores = null,
        public ?string $tipoPremio = null,
        public ?\DateTime $activatedAt = null,
        public ?\DateTime $registrationClosesAt = null,
        public ?string $estado = null,
    ) {}

    public function isRoulette(): bool
    {
        return $this->tipoDinamica === 'ruleta';
    }

    public function isTrivia(): bool
    {
        return $this->tipoDinamica === 'trivia';
    }

    public function isAvailableForRegistration(): bool
    {
        if (!$this->isActive) {
            return false;
        }

        if ($this->registrationClosesAt && $this->registrationClosesAt < new \DateTime()) {
            return false;
        }

        return true;
    }

    public function hasReachedMaxParticipants(int $currentCount): bool
    {
        if ($this->maxParticipantes === null) {
            return false;
        }

        return $currentCount >= $this->maxParticipantes;
    }
}
