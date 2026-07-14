<?php

namespace Promolider\Application\Marketing\UseCases\DinamicasPublic;

use Promolider\Domain\Marketing\Ports\Out\DinamicaRepositoryInterface;

class RegisterWinnerUseCase
{
    public function __construct(
        private DinamicaRepositoryInterface $dinamicaRepository
    ) {}

    public function execute(string $slug, string $email, ?string $premio = null): array
    {
        $dinamica = $this->dinamicaRepository->findBySlug($slug);

        if (!$dinamica) {
            throw new \RuntimeException('Dinámica no encontrada', 404);
        }

        $registro = $this->dinamicaRepository->getRegistroByEmail($dinamica->id, $email);

        if (!$registro) {
            throw new \RuntimeException('No estás registrado en esta dinámica', 403);
        }

        if ($registro->hasWon()) {
            throw new \RuntimeException('Ya has ganado anteriormente', 400);
        }

        // Marcar como ganador
        $this->dinamicaRepository->markAsWinner($registro->id, $premio);

        // Finalizar historial del turno con estado 'ganador'
        $this->dinamicaRepository->finalizeTurnHistory(
            $dinamica->id,
            $registro->id,
            'ganador',
            [
                'turno_orden' => $registro->turno ?? 0,
                'started_at' => $registro->turnoInicio ?? new \DateTime(),
                'premio_nombre' => $premio,
                'premio_tipo' => $dinamica->tipoPremio ?? null,
            ]
        );

        // Cerrar la dinámica
        $this->dinamicaRepository->deactivateDinamica($dinamica->id);

        return [
            'success' => true,
            'message' => '¡Felicidades! Has ganado.',
            'premio' => $premio,
            'slug' => $dinamica->slug,
        ];
    }
}
