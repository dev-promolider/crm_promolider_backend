<?php

namespace App\Jobs;

use App\Models\Dinamica;
use App\Models\DinamicaRegistro;
use App\Services\DinamicaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

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

    public function handle(DinamicaService $dinamicaService): void
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

        $registro->ha_jugado = true;
        $registro->save();

        $dinamicaService->finalizeTurnHistory($dinamica, $registro, 'timeout', [
            'expires_at' => $turnoExpira,
        ]);

        $dinamicaService->advanceToNextTurn($dinamica);
    }
}
