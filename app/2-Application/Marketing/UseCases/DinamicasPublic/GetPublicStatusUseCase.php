<?php

namespace Promolider\Application\Marketing\UseCases\DinamicasPublic;

use Promolider\Domain\Marketing\Ports\Out\DinamicaRepositoryInterface;

class GetPublicStatusUseCase
{
    public function __construct(
        private DinamicaRepositoryInterface $dinamicaRepository
    ) {}

    public function execute(string $slug): array
    {
        $dinamica = $this->dinamicaRepository->findBySlug($slug);

        if (!$dinamica) {
            throw new \RuntimeException('Dinámica no encontrada', 404);
        }

        $currentCount = $this->dinamicaRepository->getCurrentRegistroCount($dinamica->id);
        $hasWinner = $this->dinamicaRepository->hasWinner($dinamica->id);
        $nextTurno = $this->dinamicaRepository->getNextTurno($dinamica->id);
        $currentTurn = $this->dinamicaRepository->getCurrentTurnRegistro($dinamica->id);

        // Calcular tiempo restante (90 segundos por turno desde turno_inicio)
        $tiempoRestante = null;
        if ($currentTurn && $currentTurn['turno_inicio']) {
            $inicio = new \DateTime($currentTurn['turno_inicio']);
            $ahora = new \DateTime();
            $transcurrido = $ahora->getTimestamp() - $inicio->getTimestamp();
            $tiempoRestante = max(0, 90 - $transcurrido);
        }

        return [
            'is_active' => $dinamica->isActive,
            'is_public' => $dinamica->isPublic,
            'has_winner' => $hasWinner,
            'participants_current' => $currentCount,
            'participants_max' => $dinamica->maxParticipantes,
            'next_turno' => $nextTurno,
            'turno_actual' => $currentTurn,
            'tiempo_restante' => $tiempoRestante,
        ];
    }
}
