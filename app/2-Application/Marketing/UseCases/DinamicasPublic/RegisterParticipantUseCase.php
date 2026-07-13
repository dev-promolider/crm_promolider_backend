<?php

namespace Promolider\Application\Marketing\UseCases\DinamicasPublic;

use Promolider\Domain\Marketing\Ports\Out\DinamicaRepositoryInterface;

class RegisterParticipantUseCase
{
    public function __construct(
        private DinamicaRepositoryInterface $dinamicaRepository
    ) {}

    public function execute(string $slug, array $data): array
    {
        $dinamica = $this->dinamicaRepository->findBySlug($slug);

        if (!$dinamica) {
            throw new \RuntimeException('Dinámica no encontrada', 404);
        }

        if (!$dinamica->isAvailableForRegistration()) {
            throw new \RuntimeException('La dinámica no está disponible para registro', 400);
        }

        $currentCount = $this->dinamicaRepository->getCurrentRegistroCount($dinamica->id);

        if ($dinamica->hasReachedMaxParticipants($currentCount)) {
            throw new \RuntimeException('Se ha alcanzado el límite máximo de participantes', 400);
        }

        $existing = $this->dinamicaRepository->getRegistroByEmail($dinamica->id, $data['email']);
        if ($existing) {
            return [
                'success' => true,
                'already_registered' => true,
                'dinamica_id' => $dinamica->id,
                'registro_id' => $existing->id,
                'turno' => $existing->turno,
                'email' => $existing->email,
            ];
        }

        $hasWinner = $this->dinamicaRepository->hasWinner($dinamica->id);
        if ($hasWinner) {
            throw new \RuntimeException('La dinámica ya tiene un ganador', 400);
        }

        $nextTurno = ($this->dinamicaRepository->getNextTurno($dinamica->id) ?? 0) + 1;

        $registro = $this->dinamicaRepository->createRegistro($dinamica->id, [
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'] ?? '',
            'email' => $data['email'],
            'turno' => $nextTurno,
        ]);

        // ─── Avanzar al siguiente turno si la ruleta está activa ───
        $nextTurnData = null;
        if (
            $dinamica->isRoulette()
            && $dinamica->isActive
            && !$hasWinner
        ) {
            $turnoEnCurso = $this->dinamicaRepository->getCurrentTurnRegistro($dinamica->id);
            // Solo avanzar si NO hay un turno en progreso (sin turno_inicio)
            $hayTurnoEnCurso = $turnoEnCurso !== null && !empty($turnoEnCurso['turno_inicio']);

            if (!$hayTurnoEnCurso) {
                $nextInLine = $this->dinamicaRepository->getCurrentTurnRegistro($dinamica->id);
                if ($nextInLine) {
                    $this->dinamicaRepository->setTurnoInicio($nextInLine['id']);
                    $this->dinamicaRepository->saveTurno(
                        $dinamica->id,
                        $nextInLine['id'],
                        $nextInLine['turno'] ?? 0,
                        ['estado' => 'en_progreso']
                    );

                    $turnDuration = (int) config('services.ruleta.turn_duration', 90);
                    $expiresAt = (new \DateTime())->add(new \DateInterval("PT{$turnDuration}S"));

                    $nextTurnData = [
                        'id' => $nextInLine['id'],
                        'turno' => $nextInLine['turno'],
                        'nombre' => $nextInLine['nombre'] ?? '',
                        'apellido' => $nextInLine['apellido'] ?? '',
                        'started_at' => now()->toIso8601String(),
                        'expires_at' => $expiresAt->format('c'),
                        'duration' => $turnDuration,
                    ];
                }
            }
        }

        return [
            'success' => true,
            'already_registered' => false,
            'dinamica_id' => $dinamica->id,
            'registro_id' => $registro->id,
            'turno' => $registro->turno,
            'email' => $registro->email,
            'next_turn' => $nextTurnData,
        ];
    }
}
