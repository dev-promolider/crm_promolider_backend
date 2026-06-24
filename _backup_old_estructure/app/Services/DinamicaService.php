<?php

namespace App\Services;

use App\Events\TurnoTimerEvent;
use App\Jobs\ExpireTurnoJob;
use App\Models\Dinamica;
use App\Models\DinamicaRegistro;
use App\Models\DinamicaTurno;
use Illuminate\Support\Facades\{DB, Log, Auth, Schema};
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class DinamicaService
{
    public function getUserDinamicas($perPage = 10)
    {
        try {
            if (!Schema::hasTable('dinamicas')) {
                Log::warning('Tabla dinamicas no existe, devolviendo lista vacía');
                return collect([]);
            }

            return Dinamica::with(['premios', 'triviaConfig'])
                ->where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
        } catch (\Throwable $th) {
            Log::error('Error al obtener dinámicas del usuario', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
            ]);

            return collect([]);
        }
    }

    public function getDinamicaForEdit($id)
    {
        try {
            $dinamica = Dinamica::with('premios')->findOrFail($id);

            if ($dinamica->user_id !== Auth::id()) {
                return null;
            }

            return [
                'dinamica' => $dinamica,
                'premios' => $dinamica->premios->toArray(),
            ];
        } catch (\Throwable $th) {
            Log::error('Error al obtener dinámica para edición', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
            ]);

            return null;
        }
    }

    public function getPublicDinamicaData($slug)
    {
        $dinamica = Dinamica::with(['premios', 'triviaConfig'])
            ->where('slug', $slug)
            ->firstOrFail();

            $totalGanadores = DinamicaRegistro::where('dinamica_id', $dinamica->id)
                ->where('ha_ganado', true)
                ->count();
            $hayGanador = $totalGanadores > 0;

        if ($hayGanador && $dinamica->is_active) {
            $dinamica->update(['is_active' => false]);
        }

        $registrationDeadline = $this->calculateRegistrationDeadline($dinamica);
        $registrationSecondsLeft = $registrationDeadline
            ? now()->diffInSeconds($registrationDeadline, false)
            : null;
        $registrationLimitEnabled = $registrationDeadline !== null;
        $registrationWindowMinutes = $dinamica->tiempo_inscripcion;
        $registrationIsOpen = ! $registrationLimitEnabled || ($registrationSecondsLeft ?? 1) > 0;

        $registros = DinamicaRegistro::where('dinamica_id', $dinamica->id)
            ->orderBy('turno', 'asc')
            ->paginate(10);

        $turnoActual = null;

        if ($dinamica->is_active && !$hayGanador) {
            $turnoActual = DinamicaRegistro::where('dinamica_id', $dinamica->id)
                ->where('ha_jugado', false)
                ->where('ha_ganado', false)
                ->orderBy('turno', 'asc')
                ->first();
        }

        [$turnoActual, $turnoExpiresAt, $turnDurationSeconds, $tiempoRestante] = $this->prepareTurnoTimer(
            $dinamica,
            $turnoActual
        );

        $emailSesion = session('dinamica_email_' . $dinamica->id);
        $usuarioRegistro = null;
        $esMiTurno = false;

        if ($emailSesion) {
            $usuarioRegistro = DinamicaRegistro::where('dinamica_id', $dinamica->id)
                ->where('email', $emailSesion)
                ->first();

            if ($turnoActual && $usuarioRegistro && $turnoActual->id === $usuarioRegistro->id) {
                $esMiTurno = true;
            }
        }

        $triviaConfig = optional($dinamica->triviaConfig);
        $totalRegistrados = DinamicaRegistro::where('dinamica_id', $dinamica->id)->count();
        $limiteParticipantesAlcanzado = $dinamica->max_participantes
            ? $totalRegistrados >= $dinamica->max_participantes
            : false;
        $dinamicaCerrada = ! $registrationIsOpen || ! $dinamica->is_active;

        return [
            'dinamica' => $dinamica,
            'registros' => $registros,
            'turnoActual' => $turnoActual,
            'usuarioRegistro' => $usuarioRegistro,
            'esMiTurno' => $esMiTurno,
            'hayGanador' => $hayGanador,
            'total_ganadores' => $totalGanadores,
            'esCreador' => Auth::check() && $dinamica->user_id === Auth::id(),
            'isTrivia' => $dinamica->tipo_dinamica === 'trivia',
            'registration_config' => $triviaConfig->registration_config ?? [],
            'trivia_config' => $triviaConfig->trivia_config ?? [],
            'game_blocks' => $triviaConfig->game_blocks ?? [],
            'registration_limit_enabled' => $registrationLimitEnabled,
            'registration_window_minutes' => $registrationWindowMinutes,
            'registration_deadline' => $registrationDeadline,
            'registration_deadline_iso' => $registrationDeadline?->toIso8601String(),
            'registration_seconds_left' => $registrationSecondsLeft,
            'registration_is_open' => $registrationIsOpen,
            'dinamicaCerrada' => $dinamicaCerrada,
            'limiteParticipantesAlcanzado' => $limiteParticipantesAlcanzado,
            'turno_duration_seconds' => $turnDurationSeconds,
            'turno_started_at' => $turnoActual?->turno_inicio?->toIso8601String(),
            'turno_expires_at' => $turnoExpiresAt?->toIso8601String(),
            'turno_remaining_seconds' => $tiempoRestante,
            'tiempoRestante' => $tiempoRestante,
        ];
    }

    public function advanceToNextTurn(Dinamica $dinamica): ?array
    {
        if (!$dinamica->is_active) {
            return null;
        }

        $turnoActual = DinamicaRegistro::where('dinamica_id', $dinamica->id)
            ->where('ha_jugado', false)
            ->where('ha_ganado', false)
            ->orderBy('turno', 'asc')
            ->first();

        [$turno, $expiresAt, $duration, $remaining] = $this->prepareTurnoTimer(
            $dinamica,
            $turnoActual,
            true
        );

        if (!$turno) {
            return null;
        }

        return [
            'turno' => $turno,
            'expires_at' => $expiresAt?->toIso8601String(),
            'duration' => $duration,
            'remaining' => $remaining,
        ];
    }

    public function ensureTurnTimerForTurn(
        Dinamica $dinamica,
        ?DinamicaRegistro $turnoActual,
        bool $shouldBroadcast = false
    ): array {
        return $this->prepareTurnoTimer($dinamica, $turnoActual, $shouldBroadcast);
    }

    protected function prepareTurnoTimer(
        Dinamica $dinamica,
        ?DinamicaRegistro $turnoActual,
        bool $shouldBroadcast = false
    ): array {
        $duration = $this->getTurnDurationSeconds($dinamica);
        $expiresAt = null;

        $scheduleExpiration = false;

        if ($turnoActual) {
            if (!$turnoActual->turno_inicio) {
                $turnoActual->turno_inicio = now();
                $turnoActual->save();
                $scheduleExpiration = true;
            }

            $expiresAt = $turnoActual->turno_inicio->copy()->addSeconds($duration);

            if ($expiresAt) {
                $this->logTurnStart($dinamica, $turnoActual, $expiresAt);
            }

            if ($shouldBroadcast) {
                broadcast(new TurnoTimerEvent(
                    $dinamica,
                    $turnoActual,
                    $expiresAt,
                    $duration
                ));
                $scheduleExpiration = true;
            }
        }

        $remaining = $expiresAt ? max(0, now()->diffInSeconds($expiresAt, false)) : null;

        if ($scheduleExpiration && $turnoActual && $expiresAt) {
            ExpireTurnoJob::dispatch(
                $dinamica->id,
                $turnoActual->id,
                $turnoActual->turno_inicio->toIso8601String(),
                $duration
            )->delay($expiresAt);
        }

        return [$turnoActual, $expiresAt, $duration, $remaining];
    }

    protected function logTurnStart(
        Dinamica $dinamica,
        DinamicaRegistro $registro,
        Carbon $expiresAt
    ): void {
        DinamicaTurno::updateOrCreate(
            [
                'dinamica_id' => $dinamica->id,
                'registro_id' => $registro->id,
            ],
            [
                'turno_orden' => $registro->turno,
                'started_at' => $registro->turno_inicio,
                'expires_at' => $expiresAt,
                'estado' => 'en_progreso',
            ]
        );
    }

    public function finalizeTurnHistory(
        Dinamica $dinamica,
        DinamicaRegistro $registro,
        string $estado,
        array $extra = []
    ): void {
        $turno = DinamicaTurno::firstOrNew([
            'dinamica_id' => $dinamica->id,
            'registro_id' => $registro->id,
        ]);

        $turno->turno_orden = $turno->turno_orden ?? $registro->turno;
        $turno->started_at = $turno->started_at ?? $registro->turno_inicio;
        if (isset($extra['expires_at'])) {
            $turno->expires_at = $extra['expires_at'];
        }
        $turno->ended_at = $extra['ended_at'] ?? now();
        $turno->estado = $estado;
        if (array_key_exists('premio_nombre', $extra)) {
            $turno->premio_nombre = $extra['premio_nombre'];
        }
        if (array_key_exists('premio_tipo', $extra)) {
            $turno->premio_tipo = $extra['premio_tipo'];
        }
        if (array_key_exists('angulo', $extra)) {
            $turno->angulo = $extra['angulo'];
        }

        $turno->save();
    }

    public function recordTurnAngle(Dinamica $dinamica, DinamicaRegistro $registro, int $angle): void
    {
        DinamicaTurno::updateOrCreate(
            [
                'dinamica_id' => $dinamica->id,
                'registro_id' => $registro->id,
            ],
            [
                'turno_orden' => $registro->turno,
                'angulo' => $angle,
            ]
        );
    }

    protected function getTurnDurationSeconds(Dinamica $dinamica): int
    {
        return (int) config('services.ruleta.turn_duration', 90);
    }

    public function registerParticipant($slug, array $data)
    {
        try {
            $dinamica = Dinamica::where('slug', $slug)->firstOrFail();

            $hayGanador = DinamicaRegistro::where('dinamica_id', $dinamica->id)
                ->where('ha_ganado', true)
                ->exists();

            if ($hayGanador) {
                return [
                    'success' => false,
                    'error' => 'Esta dinámica ya tiene un ganador y ha finalizado.',
                ];
            }

            $registroExistente = DinamicaRegistro::where('dinamica_id', $dinamica->id)
                ->where('email', $data['email'])
                ->first();

            if ($registroExistente) {
                session(['dinamica_email_' . $dinamica->id => $data['email']]);
                $msg = $dinamica->tipo_dinamica === 'trivia'
                    ? 'Ya estás registrado en esta trivia. ¡Listo para jugar!'
                    : 'Ya estás registrado en esta dinámica. Turno: ' . $registroExistente->turno;
                return [
                    'success' => true,
                    'message' => $msg,
                ];
            }

            if ($dinamica->max_participantes) {
                $totalRegistrados = DinamicaRegistro::where('dinamica_id', $dinamica->id)->count();

                if ($totalRegistrados >= $dinamica->max_participantes) {
                    return [
                        'success' => false,
                        'error' => 'Se alcanzó el máximo de participantes.',
                    ];
                }
            }

            $registrationDeadline = $this->calculateRegistrationDeadline($dinamica);
            if ($registrationDeadline && now()->greaterThanOrEqualTo($registrationDeadline)) {
                return [
                    'success' => false,
                    'error' => 'El tiempo de inscripción ha finalizado.',
                ];
            }

            $ultimoTurno = DinamicaRegistro::where('dinamica_id', $dinamica->id)->max('turno') ?? 0;

            $registro = DinamicaRegistro::create([
                'dinamica_id' => $dinamica->id,
                'nombre' => $data['nombre'],
                'apellido' => $data['apellido'],
                'email' => $data['email'],
                'turno' => $dinamica->tipo_dinamica === 'trivia' ? null : $ultimoTurno + 1,
            ]);

            session(['dinamica_email_' . $dinamica->id => $data['email']]);

            if (
                $dinamica->tipo_dinamica === 'ruleta'
                && $dinamica->is_active
                && ! $hayGanador
            ) {
                $turnoEnCurso = DinamicaRegistro::where('dinamica_id', $dinamica->id)
                    ->where('ha_jugado', false)
                    ->whereNotNull('turno_inicio')
                    ->exists();

                if (! $turnoEnCurso) {
                    $this->advanceToNextTurn($dinamica);
                }
            }

            $msg = $dinamica->tipo_dinamica === 'trivia'
                ? '¡Registro exitoso! Ya puedes jugar la trivia.'
                : 'Registro exitoso. Tu turno es: ' . $registro->turno;

            return [
                'success' => true,
                'message' => $msg,
            ];
        } catch (\Throwable $th) {
            Log::error('Error al registrar participante', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
            ]);

            return [
                'success' => false,
                'error' => 'Error al procesar el registro.',
            ];
        }
    }

    public function toggleStatus($id)
    {
        try {
            $dinamica = Dinamica::findOrFail($id);

            if ($dinamica->user_id !== Auth::id()) {
                return [
                    'success' => false,
                    'message' => 'No tienes permiso para modificar esta dinámica',
                ];
            }

            $shouldActivate = ! $dinamica->is_active;
            $dinamica->is_active = $shouldActivate;

            if ($shouldActivate) {
                $now = now();
                $dinamica->activated_at = $now;

                if (
                    in_array($dinamica->modo_inscripcion, ['tiempo', 'ambos'], true)
                    && $dinamica->tiempo_inscripcion
                    && ! $dinamica->registration_closes_at
                ) {
                    $dinamica->registration_closes_at = $now->copy()->addMinutes($dinamica->tiempo_inscripcion);
                }
            } else {
                $dinamica->activated_at = null;
            }

            $dinamica->save();

            if ($shouldActivate) {
                $this->advanceToNextTurn($dinamica);
            }

            return [
                'success' => true,
                'message' => 'Estado actualizado correctamente',
            ];
        } catch (\Throwable $th) {
            Log::error('Error al cambiar estado de dinámica', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al actualizar el estado',
            ];
        }
    }

    public function delete($id)
    {
        try {
            $dinamica = Dinamica::findOrFail($id);

            if ($dinamica->user_id !== Auth::id()) {
                return [
                    'success' => false,
                    'message' => 'No tienes permiso para eliminar esta dinámica',
                ];
            }

            DB::transaction(function () use ($dinamica) {
                DinamicaTurno::where('dinamica_id', $dinamica->id)->delete();
                DinamicaRegistro::where('dinamica_id', $dinamica->id)->delete();
                $dinamica->premios()->delete();
                $dinamica->delete();
            });

            return [
                'success' => true,
                'message' => 'Dinámica eliminada correctamente',
            ];
        } catch (\Throwable $th) {
            Log::error('Error al eliminar dinámica', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
            ]);

            return [
                'success' => false,
                'message' => 'Error al eliminar: ' . $th->getMessage(),
            ];
        }
    }

    public function storeSpecifications(array $data)
    {
        try {
            $dinamica = DB::transaction(function () use ($data) {
                $isEditing = !empty($data['id']);

                if ($isEditing) {
                    $dinamica = Dinamica::findOrFail($data['id']);

                    if ($dinamica->user_id !== Auth::id()) {
                        throw new \Exception('No tienes permiso para editar esta dinámica');
                    }

                    $dinamica->update([
                        'category_id' => $data['category_id'] ?? null,
                        'nombre' => $data['nombre'],
                        'descripcion' => $data['descripcion'] ?? null,
                        'modo_inscripcion' => $data['modoInscripcion'],
                        'tiempo_inscripcion' => $data['tiempoInscripcion'] ?? null,
                        'max_participantes' => $data['maxParticipantes'] ?? null,
                        'mostrar_inscritos' => $data['mostrarInscritos'] ?? false,
                        'tipo_premio' => $data['tipoPremio'],
                        'max_ganadores' => $data['maxGanadores'],
                        'tipo_dinamica' => $dinamica->tipo_dinamica ?? 'ruleta',
                        'registration_closes_at' => null,
                    ]);

                    $dinamica->premios()->delete();
                } else {
                    $dinamica = Dinamica::create([
                        'user_id' => Auth::id(),
                        'category_id' => $data['category_id'] ?? null,
                        'slug' => Str::uuid(),
                        'nombre' => $data['nombre'],
                        'descripcion' => $data['descripcion'] ?? null,
                        'modo_inscripcion' => $data['modoInscripcion'],
                        'tiempo_inscripcion' => $data['tiempoInscripcion'] ?? null,
                        'max_participantes' => $data['maxParticipantes'] ?? null,
                        'mostrar_inscritos' => $data['mostrarInscritos'] ?? false,
                        'tipo_premio' => $data['tipoPremio'],
                        'max_ganadores' => $data['maxGanadores'],
                        'is_public' => false,
                        'is_active' => false,
                        'activated_at' => null,
                        'registration_closes_at' => null,
                        'estado' => 'draft',
                        'tipo_dinamica' => 'ruleta',
                    ]);
                }

                foreach ($data['premios'] as $premio) {
                    if (!empty($premio['vigenciaInicio']) && !empty($premio['vigenciaFin'])) {
                        $inicio = strtotime($premio['vigenciaInicio']);
                        $fin = strtotime($premio['vigenciaFin']);

                        if ($fin < $inicio) {
                            throw ValidationException::withMessages([
                                'premios' => ['La fecha fin no puede ser menor a la fecha inicio en un premio.'],
                            ]);
                        }
                    }

                    $dinamica->premios()->create([
                        'nombre' => $premio['nombre'],
                        'tipo' => $premio['tipo'],
                        'stock' => $premio['stock'],
                        'peso' => $premio['peso'],
                        'limite_usuario' => $premio['limiteUsuario'] ?? 0,
                        'vigencia_inicio' => $premio['vigenciaInicio'] ?? null,
                        'vigencia_fin' => $premio['vigenciaFin'] ?? null,
                        'claim_url' => $premio['claimUrl'] ?? null,
                    ]);
                }

                return $dinamica;
            });

            return [
                'success' => true,
                'message' => 'Dinámica guardada correctamente',
                'dinamica_id' => $dinamica->id,
                'slug' => $dinamica->slug,
            ];
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $th) {
            Log::error('Error al guardar dinámica', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
            ]);

            return [
                'success' => false,
                'message' => $th->getMessage(),
            ];
        }
    }

    public function storeTrivia(array $data)
    {
        try {
            $dinamicaId = $data['dinamicaId'] ?? null;

            $dinamica = DB::transaction(function () use ($data, $dinamicaId) {
                $registrationConfig = $data['registrationConfig'] ?? [];
                $triviaConfig = $data['triviaConfig'] ?? [];
                $gameBlocks = $data['gameBlocks'] ?? [];

                if ($dinamicaId) {
                    $dinamica = Dinamica::where('id', $dinamicaId)
                        ->where('user_id', Auth::id())
                        ->firstOrFail();

                    if ($dinamica->tipo_dinamica !== 'trivia') {
                        throw new \Exception('Solo las trivias pueden editarse desde este constructor.');
                    }

                    $attributes = $this->buildTriviaAttributes($triviaConfig, $registrationConfig, $dinamica);
                    $attributes['slug'] = $this->buildTriviaSlug($triviaConfig['slug'] ?? $dinamica->slug, $dinamica->id);
                    $attributes['estado'] = $dinamica->estado ?? 'draft';
                    $dinamica->update($attributes);
                } else {
                    $attributes = $this->buildTriviaAttributes($triviaConfig, $registrationConfig, null);
                    $dinamica = Dinamica::create(array_merge($attributes, [
                        'user_id' => Auth::id(),
                        'slug' => $this->buildTriviaSlug($triviaConfig['slug'] ?? null),
                        'estado' => 'draft',
                        'tipo_dinamica' => 'trivia',
                    ]));
                }

                $dinamica->triviaConfig()->updateOrCreate(
                    ['dinamica_id' => $dinamica->id],
                    [
                        'registration_config' => $registrationConfig,
                        'trivia_config' => $triviaConfig,
                        'game_blocks' => $this->formatTriviaGameBlocks($gameBlocks),
                    ]
                );

                return $dinamica;
            });

            $isUpdate = !empty($data['dinamicaId']);

            return [
                'success' => true,
                'message' => $isUpdate ? 'Trivia actualizada correctamente.' : 'Trivia guardada correctamente.',
                'dinamica_id' => $dinamica->id,
                'slug' => $dinamica->slug,
                'redirect' => route('marketing.dinamica.create'),
                'public_url' => route('dinamica.public', $dinamica->slug),
            ];
        } catch (\Throwable $th) {
            Log::error('Error al guardar trivia', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
            ]);

            return [
                'success' => false,
                'message' => 'No se pudo guardar la trivia.',
                'error' => $th->getMessage(),
            ];
        }
    }

    public function getTriviaDesignerData(int $dinamicaId): array
    {
        $dinamica = Dinamica::with('triviaConfig')
            ->where('id', $dinamicaId)
            ->where('user_id', Auth::id())
            ->where('tipo_dinamica', 'trivia')
            ->firstOrFail();

        $config = $dinamica->triviaConfig;
        $registrationConfig = $config->registration_config ?? [];
        $triviaConfig = $config->trivia_config ?? [];

        $triviaConfig = array_merge([
            'name' => $triviaConfig['name'] ?? $dinamica->nombre,
            'description' => $triviaConfig['description'] ?? $dinamica->descripcion,
            'slug' => $triviaConfig['slug'] ?? $dinamica->slug,
            'pointsMin' => $triviaConfig['pointsMin'] ?? 1,
            'pointsMax' => $triviaConfig['pointsMax'] ?? 10,
            'isActive' => $triviaConfig['isActive'] ?? $dinamica->is_active,
            'isPublic' => $triviaConfig['isPublic'] ?? $dinamica->is_public,
        ], $triviaConfig);

        return [
            'dinamica' => $dinamica,
            'registration_config' => $registrationConfig,
            'trivia_config' => $triviaConfig,
            'game_blocks' => $config->game_blocks ?? [],
        ];
    }

    protected function calculateRegistrationDeadline(Dinamica $dinamica): ?Carbon
    {
        $hasExplicitClose = !empty($dinamica->registration_closes_at);
        $supportsWindow = $dinamica->tiempo_inscripcion
            && in_array($dinamica->modo_inscripcion, ['tiempo', 'ambos'], true);

        if (! $supportsWindow && ! $hasExplicitClose) {
            return null;
        }

        if ($hasExplicitClose) {
            return $dinamica->registration_closes_at instanceof Carbon
                ? $dinamica->registration_closes_at->copy()
                : Carbon::parse($dinamica->registration_closes_at);
        }

        $base = $dinamica->activated_at
            ? ($dinamica->activated_at instanceof Carbon
                ? $dinamica->activated_at->copy()
                : Carbon::parse($dinamica->activated_at))
            : now();

        $deadline = $base->copy()->addMinutes($dinamica->tiempo_inscripcion);

        $dinamica->registration_closes_at = $deadline;
        $dinamica->saveQuietly();

        return $deadline;
    }

    protected function buildTriviaAttributes(array $triviaConfig, array $registrationConfig, ?Dinamica $existing = null): array
    {
        $categoryFallback = $existing ? $existing->category_id : null;
        $existingIsActive = $existing ? (bool) $existing->is_active : false;
        $isActive = (bool) ($triviaConfig['isActive'] ?? $existingIsActive);
        $activatedAt = $isActive
            ? ($existing && $existing->activated_at ? $existing->activated_at : now())
            : null;

        return [
            'category_id' => $triviaConfig['categoryId'] ?? $categoryFallback,
            'nombre' => $triviaConfig['name'],
            'descripcion' => $triviaConfig['description'] ?? null,
            'modo_inscripcion' => $this->resolveTriviaRegistrationMode($registrationConfig),
            'tiempo_inscripcion' => isset($registrationConfig['timeLimitMinutes'])
                ? (int) $registrationConfig['timeLimitMinutes']
                : null,
            'registration_closes_at' => $this->resolveRegistrationCloseAt($registrationConfig, $existing),
            'max_participantes' => isset($registrationConfig['participantsLimit'])
                ? (int) $registrationConfig['participantsLimit']
                : null,
            'mostrar_inscritos' => (bool) ($registrationConfig['showParticipants'] ?? ($existing ? (bool) $existing->mostrar_inscritos : false)),
            'tipo_premio' => $triviaConfig['prizeLabel'] ?? ($existing ? $existing->tipo_premio : 'trivia'),
            'max_ganadores' => isset($triviaConfig['maxWinners'])
                ? (int) $triviaConfig['maxWinners']
                : ($existing ? $existing->max_ganadores : null),
            'is_public' => (bool) ($triviaConfig['isPublic'] ?? ($existing ? (bool) $existing->is_public : true)),
            'is_active' => $isActive,
            'activated_at' => $activatedAt,
            'tipo_dinamica' => 'trivia',
        ];
    }

    protected function resolveTriviaRegistrationMode(array $config): string
    {
        $hasParticipantsLimit = !empty($config['participantsLimit']);
        $hasTimeWindow = !empty($config['timeLimitMinutes']);
        $hasClosingDate = !empty($config['closingDateTime']) || !empty($config['closingTime']);
        $hasAnyTimeConstraint = $hasTimeWindow || $hasClosingDate;

        if ($hasParticipantsLimit && $hasAnyTimeConstraint) {
            return 'ambos';
        }

        if ($hasAnyTimeConstraint) {
            return 'tiempo';
        }

        if ($hasParticipantsLimit) {
            return 'limite';
        }

        return 'manual';
    }

    protected function resolveRegistrationCloseAt(array $config, ?Dinamica $existing = null): ?Carbon
    {
        if (array_key_exists('closingDateTime', $config)) {
            $value = $config['closingDateTime'];

            if ($value) {
                try {
                    $timezone = config('app.timezone', 'UTC');
                    return Carbon::parse($value, $timezone);
                } catch (\Throwable $exception) {
                    Log::warning('No se pudo interpretar closingDateTime', [
                        'value' => $value,
                        'error' => $exception->getMessage(),
                    ]);
                }
            } elseif (!empty($config['closingTime'])) {
                return $this->buildCarbonFromTime($config['closingTime']);
            }

            return null;
        }

        if (!empty($config['closingTime'])) {
            return $this->buildCarbonFromTime($config['closingTime']);
        }

        return $existing?->registration_closes_at;
    }

    protected function buildCarbonFromTime(string $timeValue): ?Carbon
    {
        try {
            $timezone = config('app.timezone', 'UTC');
            [$hours, $minutes] = explode(':', $timeValue);
            $baseDate = Carbon::now($timezone)->startOfDay();
            return $baseDate->copy()->setTime((int) $hours, (int) $minutes);
        } catch (\Throwable $exception) {
            Log::warning('No se pudo construir fecha desde closingTime', [
                'value' => $timeValue,
                'error' => $exception->getMessage(),
            ]);
            return null;
        }
    }

    protected function formatTriviaGameBlocks(array $blocks): array
    {
        $normalized = array_values($blocks);

        foreach ($normalized as $index => &$block) {
            $block = [
                'title' => $block['title'] ?? 'Bloque ' . ($index + 1),
                'categoryId' => $block['categoryId'] ?? null,
                'order' => $block['order'] ?? $index + 1,
                'isActive' => (bool) ($block['isActive'] ?? true),
            ];
        }

        return $normalized;
    }

    protected function buildTriviaSlug(?string $slug, ?int $ignoreId = null): string
    {
        $base = $slug ? Str::slug($slug) : null;

        if (!$base) {
            return (string) Str::uuid();
        }

        $candidate = $base;
        $suffix = 1;

        while (
            Dinamica::where('slug', $candidate)
                ->when($ignoreId, function ($query) use ($ignoreId) {
                    $query->where('id', '!=', $ignoreId);
                })
                ->exists()
        ) {
            $candidate = $base . '-' . $suffix;
            $suffix++;
        }

        return $candidate;
    }
}
