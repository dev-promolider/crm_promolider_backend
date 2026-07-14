<?php

namespace Promolider\Application\Marketing\UseCases\DinamicasPublic;

use Promolider\Domain\Marketing\Ports\Out\DinamicaRepositoryInterface;

class MarkAsPlayedUseCase
{
    public function __construct(
        private DinamicaRepositoryInterface $dinamicaRepository
    ) {}

    public function execute(string $slug, string $email): array
    {
        $dinamica = $this->dinamicaRepository->findBySlug($slug);

        if (!$dinamica) {
            throw new \RuntimeException('Dinámica no encontrada', 404);
        }

        $registro = $this->dinamicaRepository->getRegistroByEmail($dinamica->id, $email);

        if (!$registro) {
            throw new \RuntimeException('No estás registrado en esta dinámica', 403);
        }

        // Marcar como jugado
        $this->dinamicaRepository->markAsPlayed($registro->id);

        // Finalizar historial del turno como 'sin_premio'
        $this->dinamicaRepository->finalizeTurnHistory(
            $dinamica->id,
            $registro->id,
            'sin_premio',
            [
                'turno_orden' => $registro->turno ?? 0,
                'started_at' => $registro->turnoInicio ?? new \DateTime(),
            ]
        );

        // Avanzar al siguiente turno si la dinámica sigue activa
        $nextTurnData = null;
        if ($dinamica->isActive) {
            $nextTurn = $this->dinamicaRepository->getCurrentTurnRegistro($dinamica->id);
            if ($nextTurn) {
                $this->dinamicaRepository->setTurnoInicio($nextTurn['id']);
                $this->dinamicaRepository->saveTurno(
                    $dinamica->id,
                    $nextTurn['id'],
                    $nextTurn['turno'] ?? 0,
                    ['estado' => 'en_progreso']
                );

                $turnDuration = (int) config('services.ruleta.turn_duration', 90);
                $now = new \DateTime();
                $expiresAt = (clone $now)->add(new \DateInterval("PT{$turnDuration}S"));

                $nextTurnData = [
                    'id' => $nextTurn['id'],
                    'turno' => $nextTurn['turno'],
                    'nombre' => $nextTurn['nombre'] ?? '',
                    'apellido' => $nextTurn['apellido'] ?? '',
                    'started_at' => $now->format('c'),
                    'expires_at' => $expiresAt->format('c'),
                    'duration' => $turnDuration,
                ];
            }
        }

        return [
            'success' => true,
            'message' => 'Participación registrada',
            'dinamica_id' => $dinamica->id,
            'next_turn' => $nextTurnData,
            'slug' => $dinamica->slug,
        ];
    }
}
