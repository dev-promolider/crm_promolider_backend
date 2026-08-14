<?php

namespace Promolider\Infrastructure\Marketing\Out\Persistence;

use Promolider\Domain\Marketing\Entities\Dinamica as DinamicaEntity;
use Promolider\Domain\Marketing\Entities\DinamicaRegistro as DinamicaRegistroEntity;
use Promolider\Domain\Marketing\Ports\Out\DinamicaRepositoryInterface;
use App\Models\Dinamica;
use App\Models\DinamicaPremio;
use App\Models\DinamicaRegistro;
use App\Models\DinamicaTurno;
use App\Models\DinamicaTriviaConfig;
use App\Models\TriviaUserAnswer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EloquentDinamicaRepository implements DinamicaRepositoryInterface
{
    public function findBySlug(string $slug): ?DinamicaEntity
    {
        $model = Dinamica::where('slug', $slug)->first();

        if (!$model) {
            return null;
        }

        return $this->toEntity($model);
    }

    public function getRegistros(int $dinamicaId): array
    {
        return DinamicaRegistro::where('dinamica_id', $dinamicaId)
            ->orderBy('turno')
            ->get()
            ->toArray();
    }

    public function getRegistroByEmail(int $dinamicaId, string $email): ?DinamicaRegistroEntity
    {
        $model = DinamicaRegistro::where('dinamica_id', $dinamicaId)
            ->where('email', $email)
            ->first();

        if (!$model) {
            return null;
        }

        return $this->toRegistroEntity($model);
    }

    public function getCurrentRegistroCount(int $dinamicaId): int
    {
        return DinamicaRegistro::where('dinamica_id', $dinamicaId)->count();
    }

    public function createRegistro(int $dinamicaId, array $data): DinamicaRegistroEntity
    {
        $model = DinamicaRegistro::create([
            'dinamica_id' => $dinamicaId,
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'] ?? '',
            'email' => $data['email'],
            'turno' => $data['turno'] ?? null,
        ]);

        return $this->toRegistroEntity($model);
    }

    public function markAsPlayed(int $registroId): void
    {
        DinamicaRegistro::where('id', $registroId)->update(['ha_jugado' => true]);
    }

    public function markAsWinner(int $registroId, ?string $premio): void
    {
        DinamicaRegistro::where('id', $registroId)->update([
            'ha_ganado' => true,
            'ha_jugado' => true,
            'premio_ganado' => $premio,
        ]);
    }

    public function setTurnoInicio(int $registroId): void
    {
        DinamicaRegistro::where('id', $registroId)->update([
            'turno_inicio' => now(),
        ]);
    }

    public function finalizeTurnHistory(int $dinamicaId, int $registroId, string $estado, array $extra = []): void
    {
        $turno = DinamicaTurno::firstOrNew([
            'dinamica_id' => $dinamicaId,
            'registro_id' => $registroId,
        ]);

        $turno->turno_orden = $turno->turno_orden ?? ($extra['turno_orden'] ?? 0);
        $turno->started_at = $turno->started_at ?? ($extra['started_at'] ?? now());
        $turno->ended_at = $extra['ended_at'] ?? now();
        $turno->estado = $estado;

        if (array_key_exists('premio_nombre', $extra)) {
            $turno->premio_nombre = $extra['premio_nombre'];
        }
        if (array_key_exists('premio_tipo', $extra)) {
            $turno->premio_tipo = $extra['premio_tipo'];
        }

        $turno->save();
    }

    public function deactivateDinamica(int $dinamicaId): void
    {
        Dinamica::where('id', $dinamicaId)->update(['is_active' => false]);
    }

    public function getWonPremioNames(int $dinamicaId): array
    {
        return DinamicaRegistro::where('dinamica_id', $dinamicaId)
            ->whereNotNull('premio_ganado')
            ->where('ha_ganado', true)
            ->pluck('premio_ganado')
            ->toArray();
    }

    public function getCurrentTurnRegistro(int $dinamicaId): ?array
    {
        $model = DinamicaRegistro::where('dinamica_id', $dinamicaId)
            ->where('ha_jugado', false)
            ->where('ha_ganado', false)
            ->orderBy('turno')
            ->first();

        if (!$model) {
            return null;
        }

        return [
            'id' => $model->id,
            'dinamica_id' => $model->dinamica_id,
            'nombre' => $model->nombre,
            'apellido' => $model->apellido,
            'email' => $model->email,
            'turno' => $model->turno,
            'ha_jugado' => (bool) $model->ha_jugado,
            'ha_ganado' => (bool) $model->ha_ganado,
            'turno_inicio' => $model->turno_inicio ? $model->turno_inicio->toIso8601String() : null,
            'premio_ganado' => $model->premio_ganado,
        ];
    }

    public function getNextTurno(int $dinamicaId): ?int
    {
        return DinamicaRegistro::where('dinamica_id', $dinamicaId)
            ->where('ha_jugado', false)
            ->where('ha_ganado', false)
            ->orderBy('turno')
            ->value('turno');
    }

    public function hasWinner(int $dinamicaId): bool
    {
        return DinamicaRegistro::where('dinamica_id', $dinamicaId)
            ->where('ha_ganado', true)
            ->exists();
    }

    public function getTriviaConfig(int $dinamicaId): ?array
    {
        $config = \App\Models\DinamicaTriviaConfig::where('dinamica_id', $dinamicaId)->first();

        if (!$config) {
            return null;
        }

        $registrationConfig = is_string($config->registration_config)
            ? json_decode($config->registration_config, true)
            : $config->registration_config;
        $triviaConfig = is_string($config->trivia_config)
            ? json_decode($config->trivia_config, true)
            : $config->trivia_config;
        $gameBlocks = is_string($config->game_blocks)
            ? json_decode($config->game_blocks, true)
            : $config->game_blocks;

        return [
            'registration_config' => $registrationConfig ?? [],
            'trivia_config' => $triviaConfig ?? [],
            'game_blocks' => $gameBlocks ?? [],
        ];
    }

    public function getParticipantsFeed(int $dinamicaId): array
    {
        $participants = DinamicaRegistro::where('dinamica_id', $dinamicaId)
            ->orderBy('created_at', 'desc')
            ->get(['id', 'nombre', 'apellido', 'email', 'ha_jugado', 'ha_ganado', 'turno', 'turno_inicio', 'created_at'])
            ->toArray();

        $currentTurn = $this->getCurrentTurnRegistro($dinamicaId);

        return [
            'has_winner' => $this->hasWinner($dinamicaId),
            'participants' => $participants,
            'turno_actual' => $currentTurn,
        ];
    }

    public function getActiveParticipants(int $dinamicaId): array
    {
        return DinamicaRegistro::where('dinamica_id', $dinamicaId)
            ->where('ha_jugado', false)
            ->where('ha_ganado', false)
            ->orderBy('turno')
            ->get()
            ->toArray();
    }

    public function saveTurno(int $dinamicaId, int $registroId, int $turno, array $data = []): void
    {
        // Buscar si ya existe un registro de turno para esta (dinamica, registro)
        $existing = DinamicaTurno::where('dinamica_id', $dinamicaId)
            ->where('registro_id', $registroId)
            ->first();

        $updateData = [
            'turno_orden' => $turno,
            'estado' => $data['estado'] ?? ($existing->estado ?? 'pendiente'),
        ];

        if (isset($data['angulo'])) {
            $updateData['angulo'] = $data['angulo'];
        }

        if ($existing) {
            // Actualizar solo los campos que cambiaron
            $existing->update($updateData);
        } else {
            // Crear nuevo registro con started_at solo en la primera vez
            $updateData['dinamica_id'] = $dinamicaId;
            $updateData['registro_id'] = $registroId;
            $updateData['started_at'] = now();
            DinamicaTurno::create($updateData);
        }
    }

    public function getCategoryQuestions(int $categoryId, bool $onlyActive = true): array
    {
        $query = \App\Models\QuestionItem::where('question_category_id', $categoryId)
            ->with(['options' => function ($q) {
                $q->orderBy('position');
            }]);

        if ($onlyActive) {
            $query->where('is_active', true)
                  ->where('status', 'published');
        }

        return $query->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'question_category_id' => $item->question_category_id,
                'title' => $item->title,
                'body' => $item->body,
                'status' => $item->status,
                'is_active' => (bool) $item->is_active,
                'time_limit' => $item->time_limit,
                'options' => $item->options->map(function ($opt) {
                    return [
                        'id' => $opt->id,
                        'label' => $opt->label,
                        'text' => $opt->text,
                        'is_correct' => (bool) $opt->is_correct,
                        'position' => $opt->position,
                    ];
                })->toArray(),
            ];
        })->toArray();
    }

    public function getTriviaAnswers(int $dinamicaId, int $registroId): array
    {
        return TriviaUserAnswer::where('dinamica_id', $dinamicaId)
            ->where('dinamica_registro_id', $registroId)
            ->orderBy('numero_pregunta')
            ->get()
            ->toArray();
    }

    public function saveTriviaAnswer(int $dinamicaId, int $registroId, array $data): void
    {
        $record = TriviaUserAnswer::updateOrCreate(
            [
                'dinamica_id' => $dinamicaId,
                'dinamica_registro_id' => $registroId,
                'numero_pregunta' => $data['numero_pregunta'],
            ],
            [
                'question_item_id' => $data['question_item_id'] ?? null,
                'opcion_indice' => $data['opcion_indice'] ?? null,
                'opcion_texto' => $data['opcion_texto'] ?? null,
                'es_correcta' => $data['es_correcta'] ?? false,
                'valor_pregunta' => $data['valor_pregunta'] ?? 0,
                'puntos_obtenidos' => $data['puntos_obtenidos'] ?? 0,
                'tiempo_respuesta' => $data['tiempo_respuesta'] ?? null,
            ]
        );
    }

    public function getLeaderboard(int $dinamicaId): array
    {
        return TriviaUserAnswer::select(
                'dinamica_registro_id',
                DB::raw('SUM(puntos_obtenidos) as total_puntos'),
                DB::raw('COUNT(*) as total_respondidas'),
                DB::raw('SUM(CASE WHEN es_correcta = 1 THEN 1 ELSE 0 END) as correctas'),
                DB::raw('SUM(tiempo_respuesta) as total_tiempo')
            )
            ->where('dinamica_id', $dinamicaId)
            ->groupBy('dinamica_registro_id')
            ->orderBy('total_puntos', 'desc')
            ->orderBy('total_tiempo', 'asc')
            ->get()
            ->map(function ($item) {
                $registro = DinamicaRegistro::find($item->dinamica_registro_id);
                return [
                    'id' => $registro->id ?? null,
                    'nombre' => $registro ? ($registro->nombre . ' ' . $registro->apellido) : 'Anónimo',
                    'email' => $registro->email ?? '',
                    'ha_ganado' => $registro ? (bool) $registro->ha_ganado : false,
                    'ha_jugado' => $registro ? (bool) $registro->ha_jugado : false,
                    'puntaje' => (float) $item->total_puntos,
                    'correctas' => (int) $item->correctas,
                    'total_respondidas' => (int) $item->total_respondidas,
                    'tiempo_total' => (float) ($item->total_tiempo ?? 0),
                ];
            })
            ->toArray();
    }

    // === Admin/Management methods ===

    public function getAllByUser(int $userId, ?int $courseId = null): array
    {
        $query = Dinamica::with(['premios', 'triviaConfig', 'category'])
            ->where('user_id', $userId);
            
        if ($courseId) {
            $query->where('course_id', $courseId);
        }
            
        $dinamicas = $query->orderBy('created_at', 'desc')
            ->get();

        return $dinamicas->map(function ($d) {
            $data = $d->toArray();
            $data['has_winner'] = \App\Models\DinamicaRegistro::where('dinamica_id', $d->id)
                ->where('ha_ganado', true)
                ->exists();
            if (!empty($d->slug)) {
                $data['public_url'] = url('/d/' . $d->slug);
            }
            if (!empty($d->category)) {
                $data['category_name'] = $d->category->name;
            }
            return $data;
        })->toArray();
    }

    public function findById(int $id, int $userId): ?array
    {
        $model = Dinamica::with(['premios', 'triviaConfig'])->where('id', $id)->where('user_id', $userId)->first();

        if (!$model) {
            return null;
        }

        $triviaConfig = $model->triviaConfig ? [
            'registration_config' => $model->triviaConfig->registration_config ?? [],
            'trivia_config' => $model->triviaConfig->trivia_config ?? [],
            'game_blocks' => $model->triviaConfig->game_blocks ?? [],
        ] : null;

        return [
            'dinamica' => $model->toArray(),
            'premios' => $model->premios->toArray(),
            'registration_config' => $triviaConfig['registration_config'] ?? [],
            'trivia_config' => $triviaConfig['trivia_config'] ?? null,
            'game_blocks' => $triviaConfig['game_blocks'] ?? [],
        ];
    }

    public function create(array $data): array
    {
        $model = Dinamica::create($data);
        return $model->toArray();
    }

    public function update(int $id, array $data, int $userId): array
    {
        $model = Dinamica::where('id', $id)->where('user_id', $userId)->firstOrFail();
        $model->update($data);

        return [
            'success' => true,
            'message' => 'Dinámica actualizada correctamente',
        ];
    }

    public function delete(int $id, int $userId): array
    {
        $model = Dinamica::where('id', $id)->where('user_id', $userId)->firstOrFail();

        DB::transaction(function () use ($model) {
            $model->turnos()->delete();
            $model->registros()->delete();
            $model->premios()->delete();
            $model->delete();
        });

        return [
            'success' => true,
            'message' => 'Dinámica eliminada correctamente',
        ];
    }

    public function toggleStatus(int $id, int $userId): array
    {
        $model = Dinamica::where('id', $id)->where('user_id', $userId)->firstOrFail();

        $shouldActivate = !$model->is_active;
        $model->is_active = $shouldActivate;

        if ($shouldActivate) {
            $model->activated_at = now();
        } else {
            $model->activated_at = null;
        }

        $model->save();

        return [
            'success' => true,
            'message' => 'Estado actualizado correctamente',
            'is_active' => $shouldActivate,
        ];
    }

    public function storeSpecifications(int $dinamicaId, array $data, int $userId): array
    {
        $model = Dinamica::where('id', $dinamicaId)->where('user_id', $userId)->firstOrFail();

        DB::transaction(function () use ($model, $data) {
            $model->update([
                'category_id' => $data['category_id'] ?? $model->category_id,
                'nombre' => $data['nombre'] ?? $model->nombre,
                'descripcion' => $data['descripcion'] ?? $model->descripcion,
                'modo_inscripcion' => $data['modoInscripcion'] ?? $model->modo_inscripcion,
                'tiempo_inscripcion' => $data['tiempoInscripcion'] ?? $model->tiempo_inscripcion,
                'max_participantes' => $data['maxParticipantes'] ?? $model->max_participantes,
                'mostrar_inscritos' => $data['mostrarInscritos'] ?? $model->mostrar_inscritos,
                'tipo_premio' => $data['tipoPremio'] ?? $model->tipo_premio,
                'max_ganadores' => $data['maxGanadores'] ?? $model->max_ganadores,
            ]);

            if (isset($data['premios']) && is_array($data['premios'])) {
                $model->premios()->delete();

                foreach ($data['premios'] as $premio) {
                    $model->premios()->create([
                        'nombre' => $premio['nombre'],
                        'tipo' => $premio['tipo'],
                        'stock' => $premio['stock'] ?? null,
                        'peso' => $premio['peso'] ?? null,
                        'limite_usuario' => $premio['limiteUsuario'] ?? 0,
                        'vigencia_inicio' => $premio['vigenciaInicio'] ?? null,
                        'vigencia_fin' => $premio['vigenciaFin'] ?? null,
                        'claim_url' => $premio['claimUrl'] ?? null,
                    ]);
                }
            }
        });

        return [
            'success' => true,
            'message' => 'Especificaciones guardadas correctamente',
            'dinamica_id' => $model->id,
            'slug' => $model->slug,
        ];
    }

    public function saveTriviaConfig(int $dinamicaId, array $data, int $userId): array
    {
        $model = Dinamica::where('id', $dinamicaId)->where('user_id', $userId)->firstOrFail();

        // Merge legacy fields into new format
        $triviaConfig = $data['trivia_config'] ?? $data['triviaConfig'] ?? [];
        if (empty($triviaConfig) && !empty($data['config'])) {
            $triviaConfig = $data['config'];
        }

        $gameBlocks = $data['game_blocks'] ?? $data['gameBlocks'] ?? [];
        // If no game_blocks, build from legacy questions format
        if (empty($gameBlocks) && !empty($data['questions'])) {
            // questions is an array of question_ids
            $gameBlocks = [[
                'title' => 'Bloque 1',
                'categoryId' => null,
                'order' => 1,
                'isActive' => true,
            ]];
            $triviaConfig['categoryIds'] = $data['questions'];
        }

        $registrationConfig = $data['registration_config'] ?? $data['registrationConfig'] ?? [];

        DinamicaTriviaConfig::updateOrCreate(
            ['dinamica_id' => $dinamicaId],
            [
                'registration_config' => $registrationConfig,
                'trivia_config' => $triviaConfig,
                'game_blocks' => $gameBlocks,
            ]
        );

        // Siempre actualizar nombre/descripcion si vienen en el payload
        $updateData = [];
        if (array_key_exists('nombre', $data)) {
            $updateData['nombre'] = $data['nombre'];
        }
        if (array_key_exists('descripcion', $data)) {
            $updateData['descripcion'] = $data['descripcion'];
        }
        if ($model->tipo_dinamica !== 'trivia') {
            $updateData['tipo_dinamica'] = 'trivia';
        }
        if (!empty($updateData)) {
            $model->update($updateData);
        }

        return [
            'success' => true,
            'message' => 'Trivia guardada correctamente',
            'dinamica_id' => $model->id,
            'slug' => $model->slug,
        ];
    }

    private function toEntity(Dinamica $model): DinamicaEntity
    {
        return new DinamicaEntity(
            id: $model->id,
            userId: $model->user_id,
            categoryId: $model->category_id,
            nombre: $model->nombre,
            tipoDinamica: $model->tipo_dinamica ?? 'ruleta',
            descripcion: $model->descripcion,
            slug: $model->slug,
            isActive: (bool) $model->is_active,
            isPublic: (bool) $model->is_public,
            maxParticipantes: $model->max_participantes,
            modoInscripcion: $model->modo_inscripcion,
            tiempoInscripcion: $model->tiempo_inscripcion,
            maxGanadores: $model->max_ganadores,
            tipoPremio: $model->tipo_premio,
            activatedAt: $model->activated_at ? new \DateTime($model->activated_at) : null,
            registrationClosesAt: $model->registration_closes_at ? new \DateTime($model->registration_closes_at) : null,
            estado: $model->estado,
        );
    }

    private function toRegistroEntity(DinamicaRegistro $model): DinamicaRegistroEntity
    {
        return new DinamicaRegistroEntity(
            id: $model->id,
            dinamicaId: $model->dinamica_id,
            nombre: $model->nombre,
            apellido: $model->apellido,
            email: $model->email,
            turno: $model->turno,
            haJugado: (bool) $model->ha_jugado,
            haGanado: (bool) $model->ha_ganado,
            turnoInicio: $model->turno_inicio ? new \DateTime($model->turno_inicio) : null,
            premioGanado: $model->premio_ganado,
        );
    }
}
