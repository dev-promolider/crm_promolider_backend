<?php

namespace Promolider\Domain\Marketing\Entities;

class DinamicaRegistro
{
    public function __construct(
        public ?int $id,
        public int $dinamicaId,
        public string $nombre,
        public ?string $apellido,
        public string $email,
        public ?int $turno = null,
        public bool $haJugado = false,
        public bool $haGanado = false,
        public ?\DateTime $turnoInicio = null,
        public ?string $premioGanado = null,
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
