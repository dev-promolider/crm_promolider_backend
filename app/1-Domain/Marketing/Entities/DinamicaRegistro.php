<?php

namespace Promolider\Domain\Marketing\Entities;

class DinamicaRegistro
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $dinamicaId,
        public readonly string $nombre,
        public readonly ?string $apellido,
        public readonly string $email,
        public readonly ?int $turno = null,
        public readonly bool $haJugado = false,
        public readonly bool $haGanado = false,
        public readonly ?\DateTime $turnoInicio = null,
        public readonly ?string $premioGanado = null,
    ) {}

    public function hasPlayed(): bool
    {
        return $this->haJugado;
    }

    public function hasWon(): bool
    {
        return $this->haGanado;
    }

    public function isEligibleForTurn(): bool
    {
        return !$this->haJugado && !$this->haGanado;
    }
}
