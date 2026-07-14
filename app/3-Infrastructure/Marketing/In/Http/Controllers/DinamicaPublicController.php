<?php

namespace Promolider\Infrastructure\Marketing\In\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Promolider\Application\Marketing\UseCases\DinamicasPublic\GetPublicDinamicaUseCase;
use Promolider\Application\Marketing\UseCases\DinamicasPublic\RegisterParticipantUseCase;
use Promolider\Application\Marketing\UseCases\DinamicasPublic\SpinRouletteUseCase;
use Promolider\Application\Marketing\UseCases\DinamicasPublic\GetPublicStatusUseCase;
use Promolider\Application\Marketing\UseCases\DinamicasPublic\GetParticipantsFeedUseCase;
use Promolider\Application\Marketing\UseCases\DinamicasPublic\MarkAsPlayedUseCase;
use Promolider\Application\Marketing\UseCases\DinamicasPublic\RegisterWinnerUseCase;
use Promolider\Application\Marketing\UseCases\DinamicasPublic\GetTriviaPreviewUseCase;
use Promolider\Application\Marketing\UseCases\DinamicasPublic\GetTriviaQuestionUseCase;
use Promolider\Application\Marketing\UseCases\DinamicasPublic\SubmitAnswerUseCase;
use Promolider\Application\Marketing\UseCases\DinamicasPublic\GetTriviaResultsUseCase;
use Promolider\Infrastructure\Marketing\In\Events\RuletaSpinEvent;
use Promolider\Infrastructure\Marketing\In\Events\TurnoTimerEvent;
use Promolider\Infrastructure\Marketing\In\Events\DinamicaWinnerEvent;
use Promolider\Infrastructure\Marketing\Out\Jobs\ExpireTurnoJob;

class DinamicaPublicController extends Controller
{
    const SESSION_PREFIX = 'dinamica_email_';

    private function getHttpStatus(\RuntimeException $e, int $default = 500): int
    {
        $code = $e->getCode();
        if (is_int($code) && $code >= 100 && $code <= 599) {
            return $code;
        }
        return $default;
    }

    /**
     * Resuelve el email del participante:
     * 1. Lo lee de la sesión si ya fue almacenado
     * 2. Si viene en el request, lo guarda en sesión para próximos requests
     * 3. Si no viene de ningún lado, retorna null
     *
     * Esto replica el comportamiento del monolito con Session::put/::get
     * pero manteniendo la lógica en la capa de Infrastructure (Controller).
     */
    private function resolveEmail(Request $request, string $slug): ?string
    {
        $sessionKey = self::SESSION_PREFIX . $slug;

        // 1. Intentar desde sesión (ya registrado previamente)
        if ($sessionEmail = session($sessionKey)) {
            return $sessionEmail;
        }

        // 2. Si viene en el request (no vacío), guardarlo en sesión
        if ($request->filled('email')) {
            $email = $request->input('email');
            session([$sessionKey => $email]);
            return $email;
        }

        return null;
    }

    public function __construct(
        private GetPublicDinamicaUseCase $getPublicDinamicaUseCase,
        private RegisterParticipantUseCase $registerParticipantUseCase,
        private SpinRouletteUseCase $spinRouletteUseCase,
        private GetPublicStatusUseCase $getPublicStatusUseCase,
        private GetParticipantsFeedUseCase $getParticipantsFeedUseCase,
        private MarkAsPlayedUseCase $markAsPlayedUseCase,
        private RegisterWinnerUseCase $registerWinnerUseCase,
        private GetTriviaPreviewUseCase $getTriviaPreviewUseCase,
        private GetTriviaQuestionUseCase $getTriviaQuestionUseCase,
        private SubmitAnswerUseCase $submitAnswerUseCase,
        private GetTriviaResultsUseCase $getTriviaResultsUseCase
    ) {}

    /**
     * GET /d/{slug}
     * Muestra la landing pública de una dinámica.
     */
    public function show(string $slug)
    {
        try {
            $data = $this->getPublicDinamicaUseCase->execute($slug);
            return response()->json($data);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], $this->getHttpStatus($e, 404));
        }
    }

    /**
     * POST /d/{slug}/register
     * Registra un participante público y guarda su email en sesión.
     */
    public function register(Request $request, string $slug)
    {
        try {
            $data = $request->validate([
                'nombre' => 'required|string|max:100',
                'apellido' => 'nullable|string|max:100',
                'email' => 'required|email|max:255',
            ]);

            // Guardar email en sesión (como el monolito)
            session([self::SESSION_PREFIX . $slug => $data['email']]);

            $result = $this->registerParticipantUseCase->execute($slug, $data);

            // Si hay un siguiente turno (ruleta activa), emitir evento TurnoTimerEvent y programar expiración
            if (!empty($result['next_turn'])) {
                $nextTurn = $result['next_turn'];
                try {
                    broadcast(new TurnoTimerEvent(
                        $slug,
                        [
                            'id' => $nextTurn['id'],
                            'turno' => $nextTurn['turno'],
                            'nombre' => $nextTurn['nombre'],
                            'apellido' => $nextTurn['apellido'],
                        ],
                        $nextTurn['started_at'],
                        $nextTurn['expires_at'],
                        $nextTurn['duration']
                    ));

                    // Programar job de expiración
                    ExpireTurnoJob::dispatch(
                        $result['dinamica_id'] ?? $result['registro_id'],
                        $nextTurn['id'],
                        $nextTurn['started_at'],
                        $nextTurn['duration']
                    )->delay(\Carbon\Carbon::parse($nextTurn['expires_at']));
                } catch (\Throwable $e) {
                    Log::warning('Error broadcasting TurnoTimerEvent after registration: ' . $e->getMessage());
                }
            }

            return response()->json($result, $result['already_registered'] ? 200 : 201);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], $this->getHttpStatus($e, 400));
        }
    }

    /**
     * POST /d/{slug}/spin
     * Realiza el giro de la ruleta. Email opcional (se resuelve de sesión).
     */
    public function spin(Request $request, string $slug)
    {
        try {
            $email = $this->resolveEmail($request, $slug);

            if (!$email) {
                return response()->json(['message' => 'Email requerido. Regístrate primero o proporciona tu email.'], 400);
            }

            $result = $this->spinRouletteUseCase->execute($slug, $email);

            // Emitir evento WebSocket RuletaSpinEvent
            if (!empty($result['angle'])) {
                try {
                    broadcast(new RuletaSpinEvent($result['angle'], $slug, $result['registro_id']));
                } catch (\Throwable $e) {
                    Log::warning('Error broadcasting RuletaSpinEvent: ' . $e->getMessage());
                }
            }

            return response()->json($result);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], $this->getHttpStatus($e, 400));
        }
    }

    /**
     * GET /d/{slug}/status
     * Obtiene el estado público de la dinámica.
     */
    public function status(string $slug)
    {
        try {
            $data = $this->getPublicStatusUseCase->execute($slug);
            return response()->json($data);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], $this->getHttpStatus($e, 404));
        }
    }

    /**
     * GET /d/{slug}/participants-feed
     * Obtiene el feed de participantes.
     */
    public function participantsFeed(string $slug)
    {
        try {
            $data = $this->getParticipantsFeedUseCase->execute($slug);
            return response()->json($data);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], $this->getHttpStatus($e, 404));
        }
    }

    /**
     * POST /d/{slug}/marcar-jugado
     * Marca al participante como que ya jugó. Email opcional (se resuelve de sesión).
     */
    public function marcarJugado(Request $request, string $slug)
    {
        try {
            $email = $this->resolveEmail($request, $slug);

            if (!$email) {
                return response()->json(['message' => 'Email requerido. Regístrate primero o proporciona tu email.'], 400);
            }

            $result = $this->markAsPlayedUseCase->execute($slug, $email);

            // Emitir evento TurnoTimerEvent si hay siguiente turno
            if (!empty($result['next_turn'])) {
                $nextTurn = $result['next_turn'];
                try {
                    broadcast(new TurnoTimerEvent(
                        $slug,
                        [
                            'id' => $nextTurn['id'],
                            'turno' => $nextTurn['turno'],
                            'nombre' => $nextTurn['nombre'],
                            'apellido' => $nextTurn['apellido'],
                        ],
                        $nextTurn['started_at'],
                        $nextTurn['expires_at'],
                        $nextTurn['duration']
                    ));

                    // Programar job de expiración
                    ExpireTurnoJob::dispatch(
                        $result['dinamica_id'] ?? $nextTurn['id'],
                        $nextTurn['id'],
                        $nextTurn['started_at'],
                        $nextTurn['duration']
                    )->delay(\Carbon\Carbon::parse($nextTurn['expires_at']));
                } catch (\Throwable $e) {
                    Log::warning('Error broadcasting TurnoTimerEvent after markAsPlayed: ' . $e->getMessage());
                }
            }

            return response()->json($result);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], $this->getHttpStatus($e, 400));
        }
    }

    /**
     * POST /d/{slug}/registrar-ganador
     * Registra al ganador de la dinámica. Email opcional (se resuelve de sesión).
     */
    public function registrarGanador(Request $request, string $slug)
    {
        try {
            $email = $this->resolveEmail($request, $slug);

            if (!$email) {
                return response()->json(['message' => 'Email requerido. Regístrate primero o proporciona tu email.'], 400);
            }

            $premio = $request->input('premio');

            $result = $this->registerWinnerUseCase->execute($slug, $email, $premio);

            // Emitir evento WebSocket DinamicaWinnerEvent
            try {
                broadcast(new DinamicaWinnerEvent(
                    $slug,
                    $result['message'],
                    $result['premio'] ?? $premio
                ));
            } catch (\Throwable $e) {
                Log::warning('Error broadcasting DinamicaWinnerEvent: ' . $e->getMessage());
            }

            return response()->json($result);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], $this->getHttpStatus($e, 400));
        }
    }

    // ─── TRIVIA ──────────────────────────────────────────────────────────────

    /**
     * GET /d/{slug}/preview
     * Vista previa de la trivia. Email opcional (se resuelve de sesión).
     */
    public function triviaPreview(Request $request, string $slug)
    {
        try {
            $email = $this->resolveEmail($request, $slug);

            if (!$email) {
                return response()->json(['message' => 'Email requerido.'], 400);
            }

            $result = $this->getTriviaPreviewUseCase->execute($slug, $email);

            return response()->json($result);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], $this->getHttpStatus($e, 404));
        }
    }

    /**
     * GET /d/{slug}/pregunta/{numero}
     * Obtiene una pregunta de la trivia. Email opcional (se resuelve de sesión).
     */
    public function triviaQuestion(Request $request, string $slug, int $numero)
    {
        try {
            $email = $this->resolveEmail($request, $slug);

            if (!$email) {
                return response()->json(['message' => 'Email requerido.'], 400);
            }

            $result = $this->getTriviaQuestionUseCase->execute($slug, $numero, $email);

            return response()->json($result);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], $this->getHttpStatus($e, 404));
        }
    }

    /**
     * POST /d/{slug}/pregunta/{numero}/respuesta
     * Envía la respuesta a una pregunta de trivia. Email opcional (se resuelve de sesión).
     */
    public function submitAnswer(Request $request, string $slug, int $numero)
    {
        try {
            $email = $this->resolveEmail($request, $slug);

            if (!$email) {
                return response()->json(['message' => 'Email requerido.'], 400);
            }

            $payload = $request->validate([
                'opcion_index' => 'nullable|integer|min:0',
                'timeout' => 'sometimes|boolean',
                'elapsed_ms' => 'nullable|numeric|min:0',
            ]);

            $result = $this->submitAnswerUseCase->execute($slug, $numero, $email, $payload);

            return response()->json($result);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], $this->getHttpStatus($e, 400));
        }
    }

    /**
     * GET /d/{slug}/resultados
     * Obtiene los resultados/leaderboard de la trivia.
     */
    public function triviaResults(string $slug)
    {
        try {
            $result = $this->getTriviaResultsUseCase->execute($slug);
            return response()->json($result);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], $this->getHttpStatus($e, 404));
        }
    }
}
