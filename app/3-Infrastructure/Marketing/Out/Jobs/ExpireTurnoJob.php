<?php

namespace Promolider\Infrastructure\Marketing\Out\Jobs;

use App\Models\Dinamica;
use App\Models\DinamicaRegistro;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Promolider\Infrastructure\Marketing\In\Events\TurnoTimerEvent;
use Promolider\Domain\Marketing\Ports\Out\DinamicaRepositoryInterface;

class ExpireTurnoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $dinamicaId;
    protected int $registroId;
    protected string $turnoInicioIso;
    protected int $durationSeconds;

    public function __construct(int $dinamicaId, int $registroId, string $turnoInicioIso, int $durationSeconds)
    {
        $this->dinamicaId = $dinamicaId;
        $this->registroId = $registroId;
        $this->turnoInicioIso = $turnoInicioIso;
        $this->durationSeconds = $durationSeconds;
    }

    public function handle(DinamicaRepositoryInterface $dinamicaRepository): void
    {
        $dinamica = Dinamica::find($this->dinamicaId);
        if (!$dinamica || !$dinamica->is_active) {
            return;
        }

        $registro = DinamicaRegistro::find($this->registroId);
        if (!$registro || $registro->ha_jugado || $registro->ha_ganado) {
            return;
        }

        $turnoInicioEsperado = Carbon::parse($this->turnoInicioIso);
        if (!$registro->turno_inicio || !$registro->turno_inicio->equalTo($turnoInicioEsperado)) {
            return;
        }

        $turnoExpira = $turnoInicioEsperado->copy()->addSeconds($this->durationSeconds);
        if (now()->lessThan($turnoExpira)) {
            return;
        }

        // Marcar como jugado por timeout
        $registro->ha_jugado = true;
        $registro->save();

        // Finalizar historial del turno como timeout
        $dinamicaRepository->finalizeTurnHistory(
            $this->dinamicaId,
            $this->registroId,
            'timeout',
            [
                'expires_at' => $turnoExpira,
            ]
        );

        // Avanzar al siguiente turno si la dinámica sigue activa
        if ($dinamica->is_active) {
            $nextTurn = $dinamicaRepository->getCurrentTurnRegistro($this->dinamicaId);
            if ($nextTurn) {
                $dinamicaRepository->setTurnoInicio($nextTurn['id']);
                $dinamicaRepository->saveTurno(
                    $this->dinamicaId,
                    $nextTurn['id'],
                    $nextTurn['turno'] ?? 0,
                    ['estado' => 'en_progreso']
                );

                // Broadcaster TurnoTimerEvent
                $expiresAt = now()->addSeconds($this->durationSeconds);
                try {
                    broadcast(new TurnoTimerEvent(
                        $dinamica->slug,
                        $nextTurn,
                        now()->toIso8601String(),
                        $expiresAt->toIso8601String(),
                        $this->durationSeconds
                    ));
                } catch (\Throwable $e) {
                    Log::warning('Error broadcasting TurnoTimerEvent from ExpireTurnoJob: ' . $e->getMessage());
                }

                // Programar el próximo Job de expiración
                self::dispatch(
                    $this->dinamicaId,
                    $nextTurn['id'],
                    now()->toIso8601String(),
                    $this->durationSeconds
                )->delay($expiresAt);
            }
        }
    }
}
