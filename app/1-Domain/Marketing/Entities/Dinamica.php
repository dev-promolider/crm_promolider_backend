<?php

namespace Promolider\Domain\Marketing\Entities;

class Dinamica
{
    public function __construct(
        public readonly ?int $id,
        public readonly ?int $userId,
        public readonly ?int $categoryId,
        public readonly string $nombre,
        public readonly string $tipoDinamica,
        public readonly ?string $descripcion,
        public readonly ?string $slug,
        public readonly bool $isActive = false,
        public readonly bool $isPublic = false,
        public readonly ?int $maxParticipantes = null,
        public readonly ?string $modoInscripcion = null,
        public readonly ?int $tiempoInscripcion = null,
        public readonly ?int $maxGanadores = null,
        public readonly ?string $tipoPremio = null,
        public readonly ?\DateTime $activatedAt = null,
        public readonly ?\DateTime $registrationClosesAt = null,
        public readonly ?string $estado = null,
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
