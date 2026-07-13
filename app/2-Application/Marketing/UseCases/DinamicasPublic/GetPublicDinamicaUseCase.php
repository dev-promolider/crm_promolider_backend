<?php

namespace Promolider\Application\Marketing\UseCases\DinamicasPublic;

use Promolider\Domain\Marketing\Ports\Out\DinamicaRepositoryInterface;

class GetPublicDinamicaUseCase
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

        $registros = $this->dinamicaRepository->getRegistros($dinamica->id);

        // Obtener premios desde el modelo Eloquent
        $model = \App\Models\Dinamica::where('slug', $slug)->with('premios')->first();
        $tipoPremio = null;
        $premiosArr = [];

        if ($model) {
            $tipoPremio = $model->tipo_premio;

            // Filtrar premios ya ganados (excepto tipo 'vacio')
            $premiosGanados = $this->dinamicaRepository->getWonPremioNames($dinamica->id);

            $premiosArr = $model->premios->filter(function ($p) use ($premiosGanados) {
                return $p->tipo === 'vacio' || !in_array($p->nombre, $premiosGanados);
            })->map(function ($p) {
                return [
                    'id' => $p->id,
                    'nombre' => $p->nombre,
                    'tipo' => $p->tipo,
                    'stock' => $p->stock,
                    'peso' => $p->peso,
                    'limite_usuario' => $p->limite_usuario,
                    'claim_url' => $p->claim_url,
                ];
            })->values()->toArray();
        }

        $hasWinner = $this->dinamicaRepository->hasWinner($dinamica->id);
        $nextTurno = $this->dinamicaRepository->getNextTurno($dinamica->id);
        $currentTurn = $this->dinamicaRepository->getCurrentTurnRegistro($dinamica->id);
        $triviaConfig = null;

        if ($dinamica->isTrivia()) {
            $triviaConfig = $this->dinamicaRepository->getTriviaConfig($dinamica->id);
        }

        // Calcular tiempo restante del turno actual
        $tiempoRestante = null;
        $turnoDuration = (int) config('services.ruleta.turn_duration', 90);
        if ($currentTurn && !empty($currentTurn['turno_inicio'])) {
            $inicio = new \DateTime($currentTurn['turno_inicio']);
            $ahora = new \DateTime();
            $transcurrido = $ahora->getTimestamp() - $inicio->getTimestamp();
            $tiempoRestante = max(0, $turnoDuration - $transcurrido);
        }

        // Mapear registros como array de participantes individuales
        $participants = array_map(function ($r) {
            return [
                'id' => $r['id'],
                'nombre' => $r['nombre'],
                'apellido' => $r['apellido'] ?? '',
                'email' => $r['email'],
                'turno' => $r['turno'],
                'ha_jugado' => (bool) ($r['ha_jugado'] ?? false),
                'ha_ganado' => (bool) ($r['ha_ganado'] ?? false),
            ];
        }, $registros);

        return [
            'dinamica' => [
                'id' => $dinamica->id,
                'nombre' => $dinamica->nombre,
                'tipo_dinamica' => $dinamica->tipoDinamica,
                'descripcion' => $dinamica->descripcion,
                'slug' => $dinamica->slug,
                'is_active' => $dinamica->isActive,
                'is_public' => $dinamica->isPublic,
                'max_participantes' => $dinamica->maxParticipantes,
                'modo_inscripcion' => $dinamica->modoInscripcion,
                'registration_closes_at' => $dinamica->registrationClosesAt?->format('c'),
                'tipo_premio' => $tipoPremio,
                'premios' => $premiosArr,
            ],
            'participants' => $participants,
            'total_participants' => count($participants),
            'has_winner' => $hasWinner,
            'next_turno' => $nextTurno,
            'turno_actual' => $currentTurn,
            'trivia_config' => $triviaConfig,
            // Timer info
            'turno_duration_seconds' => $turnoDuration,
            'turno_remaining_seconds' => $tiempoRestante,
        ];
    }
}
