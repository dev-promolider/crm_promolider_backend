<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use App\Services\DinamicaService;
use App\Services\QuestionCategoryService;
use App\Models\DinamicaRegistro;
use App\Models\TriviaUserAnswer;

class PublicTriviaController extends Controller
{
    /**
     * Muestra una pregunta individual de la trivia pública
     */
    public function pregunta(Request $request, $slug, $numero)
    {
        $data = $this->dinamicaService->getPublicDinamicaData($slug);
        // Decodificar trivia_config si es string JSON
        if (isset($data['trivia_config']) && is_string($data['trivia_config'])) {
            $data['trivia_config'] = json_decode($data['trivia_config'], true);
        }
        $data['game_blocks'] = $this->normalizeGameBlocks($data['game_blocks'] ?? []);
        if (!($data['dinamica']->is_active ?? false) || !$data['usuarioRegistro']) {
            abort(403, 'No autorizado o trivia no activa');
        }
        $meta = $this->buildTriviaMeta($data['trivia_config'] ?? [], $data['game_blocks'] ?? []);
        $preguntas = $meta['preguntas'];
        $cantidadPreguntas = $preguntas->count();
        $indice = intval($numero) - 1;
        if ($indice < 0 || $indice >= $cantidadPreguntas) {
            return redirect()->route('dinamica.public.preview', ['slug' => $slug])
                ->with('error', 'Pregunta no encontrada');
        }

        $pregunta = $preguntas->get($indice);
        $opciones = $pregunta ? $pregunta->options->pluck('text')->toArray() : [];
        $valorPregunta = $meta['puntajes'][$indice] ?? ($meta['puntajeTotal'] / max(1, $cantidadPreguntas));

        \Log::info('DEBUG pregunta', [
            'pregunta_id' => $pregunta?->id,
            'pregunta_valor' => $pregunta ? ($pregunta->body ?: $pregunta->title) : null,
            'opciones' => $opciones,
        ]);
        return view('content.marketing.dinamica.pregunta', [
            'categoria' => $meta['categoria'],
            'numeroPregunta' => $numero,
            'pregunta' => $pregunta ? ($pregunta->body ?: $pregunta->title) : null,
            'valorPregunta' => $valorPregunta,
            'opciones' => $opciones,
            'usuarioId' => optional($data['usuarioRegistro'])->id,
            'cantidadPreguntas' => $cantidadPreguntas,
            'puntajeTotal' => $meta['puntajeTotal'],
            'tiempoPregunta' => $meta['tiempoPregunta'],
            'resultadosUrl' => route('dinamica.public.resultados', ['slug' => $slug]),
        ]);
    }

    public function responderPregunta(Request $request, $slug, $numero)
    {
        $data = $this->dinamicaService->getPublicDinamicaData($slug);
        if (isset($data['trivia_config']) && is_string($data['trivia_config'])) {
            $data['trivia_config'] = json_decode($data['trivia_config'], true);
        }
        $data['game_blocks'] = $this->normalizeGameBlocks($data['game_blocks'] ?? []);

        $registro = $data['usuarioRegistro'];
        if (!($data['dinamica']->is_active ?? false) || !$registro) {
            return response()->json(['message' => 'La trivia no está disponible.'], 403);
        }

        $meta = $this->buildTriviaMeta($data['trivia_config'] ?? [], $data['game_blocks'] ?? []);
        $preguntas = $meta['preguntas'];
        $indice = intval($numero) - 1;

        if ($indice < 0 || $indice >= $preguntas->count()) {
            return response()->json(['message' => 'Pregunta no encontrada.'], 404);
        }

        $validated = $request->validate([
            'opcion_index' => 'nullable|integer|min:0',
            'timeout' => 'sometimes|boolean',
            'elapsed_ms' => 'nullable|numeric|min:0',
        ]);

        $timeout = (bool) ($validated['timeout'] ?? false);
        $opcionIndex = $validated['opcion_index'] ?? null;

        if (!$timeout && $opcionIndex === null) {
            return response()->json(['message' => 'Selecciona una opción antes de enviar.'], 422);
        }

        $pregunta = $preguntas->get($indice);
        $opciones = $pregunta->options->values();

        $opcionElegida = null;
        if (!$timeout) {
            if (! isset($opciones[$opcionIndex])) {
                return response()->json(['message' => 'La opción seleccionada no es válida.'], 422);
            }
            $opcionElegida = $opciones[$opcionIndex];
        }

        $esCorrecta = $timeout ? false : (bool) $opcionElegida->is_correct;
        $valorPregunta = (float) ($meta['puntajes'][$indice] ?? 0);
        $puntosObtenidos = $esCorrecta ? $valorPregunta : 0.0;
        $tiempoLimite = max(5, (int) ($meta['tiempoPregunta'] ?? 30));
        $elapsedMs = max(0, (float) ($validated['elapsed_ms'] ?? 0));
        $tiempoRespuesta = min($tiempoLimite, round($elapsedMs / 1000, 2));
        if ($timeout && $tiempoRespuesta <= 0) {
            $tiempoRespuesta = $tiempoLimite;
        }

        TriviaUserAnswer::updateOrCreate(
            [
                'dinamica_registro_id' => $registro->id,
                'question_item_id' => $pregunta->id,
            ],
            [
                'dinamica_id' => $data['dinamica']->id,
                'numero_pregunta' => (int) $numero,
                'opcion_indice' => $opcionIndex,
                'opcion_texto' => $opcionElegida?->text,
                'es_correcta' => $esCorrecta,
                'valor_pregunta' => $valorPregunta,
                'puntos_obtenidos' => $puntosObtenidos,
                'tiempo_respuesta' => $tiempoRespuesta,
            ]
        );

        if (! $registro->ha_jugado) {
            $registro->update(['ha_jugado' => true]);
        }

        $puntajeAcumulado = TriviaUserAnswer::where('dinamica_registro_id', $registro->id)->sum('puntos_obtenidos');
        $respuestasContestadas = TriviaUserAnswer::where('dinamica_registro_id', $registro->id)->count();
        $completado = $meta['cantidadCasillas'] > 0 && $respuestasContestadas >= $meta['cantidadCasillas'];

        return response()->json([
            'correcta' => $esCorrecta,
            'puntajePregunta' => $valorPregunta,
            'puntosObtenidos' => $puntosObtenidos,
            'puntajeAcumulado' => $puntajeAcumulado,
            'preguntasContestadas' => $respuestasContestadas,
            'completado' => $completado,
            'timeout' => $timeout,
            'previewUrl' => route('dinamica.public.preview', ['slug' => $slug]),
            'resultadosUrl' => $completado ? route('dinamica.public.resultados', ['slug' => $slug]) : null,
        ]);
    }
    protected $dinamicaService;
    protected $questionCategoryService;

    public function __construct(DinamicaService $dinamicaService, QuestionCategoryService $questionCategoryService)
    {
        $this->dinamicaService = $dinamicaService;
        $this->questionCategoryService = $questionCategoryService;
    }

    /**
     * Muestra el preview real de la trivia pública (solo si está activa y el usuario está registrado)
     */
    public function preview(Request $request, $slug)
    {
        $data = $this->dinamicaService->getPublicDinamicaData($slug);
        if (isset($data['trivia_config']) && is_string($data['trivia_config'])) {
            $data['trivia_config'] = json_decode($data['trivia_config'], true);
        }
        $data['game_blocks'] = $this->normalizeGameBlocks($data['game_blocks'] ?? []);
        // Solo mostrar si está activa y el usuario está registrado
        if (!($data['dinamica']->is_active ?? false) || !$data['usuarioRegistro']) {
            abort(403, 'No autorizado o trivia no activa');
        }
        $meta = $this->buildTriviaMeta($data['trivia_config'] ?? [], $data['game_blocks'] ?? []);
        $playerProgress = $this->buildPlayerProgress($data['dinamica'], $data['usuarioRegistro'], $meta);
        \Log::info('TRIVIA PREVIEW META', [
            'slug' => $slug,
            'cantidad_casillas' => $meta['cantidadCasillas'],
            'preguntas' => $meta['preguntas']->count(),
        ]);
        return view('content.marketing.dinamica.preview', [
            'numeroPregunta' => 1,
            'nombreSeccion' => 'General',
            'categoria' => $meta['categoria'],
            'estadoInteraccion' => 'espera',
            'cantidadCasillas' => $meta['cantidadCasillas'],
            'preguntas' => $meta['preguntas'],
            'puntajes' => $meta['puntajes'],
            'puntajeTotal' => $meta['puntajeTotal'],
            'usuarioId' => optional($data['usuarioRegistro'])->id,
            'resultadosUrl' => route('dinamica.public.resultados', ['slug' => $slug]),
            'blocksProgress' => $playerProgress['blocks'],
            'nextPlayableIndex' => $playerProgress['nextPlayableIndex'],
            'lastCompletedIndex' => $playerProgress['lastCompletedIndex'],
            'totalScore' => $playerProgress['totalScore'],
            'answeredNumbers' => $playerProgress['answeredNumbers'],
            'hasMultipleBlocks' => $playerProgress['hasMultipleBlocks'],
        ]);
    }

    public function resultados(Request $request, $slug)
    {
        $data = $this->dinamicaService->getPublicDinamicaData($slug);
        if (isset($data['trivia_config']) && is_string($data['trivia_config'])) {
            $data['trivia_config'] = json_decode($data['trivia_config'], true);
        }
        $data['game_blocks'] = $this->normalizeGameBlocks($data['game_blocks'] ?? []);

        if (!$data['usuarioRegistro']) {
            abort(403, 'Regístrate en la trivia para ver los resultados.');
        }

        $meta = $this->buildTriviaMeta($data['trivia_config'] ?? [], $data['game_blocks'] ?? []);
        $dinamica = $data['dinamica'];

        $participantes = DinamicaRegistro::where('dinamica_id', $dinamica->id)
            ->orderByDesc('ha_ganado')
            ->orderByDesc('ha_jugado')
            ->orderBy('created_at')
            ->get();

        $leaderboard = $this->buildLeaderboard(
            $participantes,
            $meta['puntajeTotal'],
            $meta['blocks'] ?? [],
            $meta['question_block_map'] ?? []
        );
        $winner = collect($leaderboard)->firstWhere('haGanado', true);

        return view('content.marketing.dinamica.resultados', [
            'dinamica' => [
                'nombre' => $dinamica->nombre,
                'descripcion' => $dinamica->descripcion,
                'slug' => $dinamica->slug,
            ],
            'categoria' => $meta['categoria'],
            'puntajes' => array_values($meta['puntajes']),
            'puntajeTotal' => $meta['puntajeTotal'],
            'leaderboard' => $leaderboard,
            'winner' => $winner,
            'usuarioId' => optional($data['usuarioRegistro'])->id,
            'totalParticipantes' => $participantes->count(),
            'casillasTotales' => $meta['cantidadCasillas'],
            'blocks' => $meta['blocks'] ?? [],
            'slug' => $slug,
        ]);
    }

    protected function normalizeGameBlocks($gameBlocks): array
    {
        if ($gameBlocks instanceof Arrayable) {
            $gameBlocks = $gameBlocks->toArray();
        }

        if (is_string($gameBlocks)) {
            $decoded = json_decode($gameBlocks, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $gameBlocks = $decoded;
            } else {
                return [];
            }
        }

        if ($gameBlocks instanceof \Illuminate\Support\Collection) {
            $gameBlocks = $gameBlocks->toArray();
        }

        if (! is_array($gameBlocks)) {
            return [];
        }

        $normalized = [];

        foreach ($gameBlocks as $block) {
            if ($block instanceof Arrayable) {
                $block = $block->toArray();
            } elseif (is_object($block)) {
                $block = (array) $block;
            }

            if (! is_array($block)) {
                continue;
            }

            $categoryId = $block['categoryId'] ?? $block['category_id'] ?? null;
            if ($categoryId === null || $categoryId === '') {
                continue;
            }

            $normalized[] = [
                'title' => $block['title'] ?? ($block['name'] ?? null),
                'categoryId' => (int) $categoryId,
                'order' => isset($block['order']) ? (int) $block['order'] : null,
                'isActive' => array_key_exists('isActive', $block)
                    ? (bool) $block['isActive']
                    : (array_key_exists('is_active', $block) ? (bool) $block['is_active'] : true),
            ];
        }

        return $normalized;
    }

    protected function buildTriviaMeta(?array $triviaConfig = null, array $gameBlocks = []): array
    {
        $triviaConfig = $triviaConfig ?? [];
        $puntajeTotal = (float) ($triviaConfig['pointsMax'] ?? 20);
        $tiempoPregunta = (int) ($triviaConfig['questionTimeLimit'] ?? 30);
        $categoria = $triviaConfig['categoria'] ?? 'Cultura General';
        $preguntas = collect();

        $blocksLineup = $this->buildBlockLineup($gameBlocks, $triviaConfig);
        $blocksSummary = [];
        $questionBlockMap = [];
        $blockQuestionNumbers = [];
        $categoryCache = [];
        $questionCounter = 0;

        if (!empty($blocksLineup)) {
            foreach ($blocksLineup as $index => $block) {
                $categoryId = (int) ($block['categoryId'] ?? 0);
                if ($categoryId <= 0) {
                    continue;
                }

                if (!array_key_exists($categoryId, $categoryCache)) {
                    $categoryCache[$categoryId] = $this->fetchCategoryQuestions($categoryId);
                }

                $categoryData = $categoryCache[$categoryId];
                if (!$categoryData) {
                    continue;
                }

                if ($preguntas->isEmpty()) {
                    $categoria = $categoryData['nombre'];
                }

                $questions = $categoryData['preguntas'];
                if ($questions->isEmpty()) {
                    continue;
                }

                $questionIds = $questions->pluck('id')->all();

                $blocksSummary[] = [
                    'index' => $index,
                    'title' => $block['title'] ?? ('Bloque ' . ($index + 1)),
                    'categoryId' => $categoryId,
                    'categoryName' => $categoryData['nombre'],
                    'questionCount' => count($questionIds),
                ];
                $blockQuestionNumbers[$index] = $blockQuestionNumbers[$index] ?? [];

                foreach ($questions as $question) {
                    $questionCounter++;
                    $preguntas->push($question);
                    $questionBlockMap[$question->id] = $index;
                    $blockQuestionNumbers[$index][] = $questionCounter;
                }
            }
        } else {
            $categoryIds = [];
            if (!empty($triviaConfig['categoryId'])) {
                $categoryIds[] = (int) $triviaConfig['categoryId'];
            }

            if (empty($categoryIds)) {
                $categoryIds = $this->extractCategoryIdsFromBlocks($gameBlocks);
            }

            foreach ($categoryIds as $index => $categoryId) {
                $categoryData = $this->fetchCategoryQuestions($categoryId);
                if (!$categoryData) {
                    continue;
                }

                if ($index === 0) {
                    $categoria = $categoryData['nombre'];
                }

                if ($categoryData['preguntas']->isNotEmpty()) {
                    foreach ($categoryData['preguntas'] as $question) {
                        $questionCounter++;
                        $preguntas->push($question);
                        $questionBlockMap[$question->id] = 0;
                    }
                }
            }
        }

        if (empty($blockQuestionNumbers) && $preguntas->isNotEmpty()) {
            $blockQuestionNumbers[0] = range(1, $preguntas->count());
        }

        $cantidadCasillas = $preguntas->count();
        $puntajes = $this->buildPointDistribution($preguntas, $puntajeTotal, $triviaConfig);

        return [
            'categoria' => $categoria,
            'preguntas' => $preguntas,
            'puntajes' => $puntajes,
            'puntajeTotal' => $puntajeTotal,
            'cantidadCasillas' => $cantidadCasillas,
            'tiempoPregunta' => max(5, $tiempoPregunta),
            'blocks' => $blocksSummary,
            'question_block_map' => $questionBlockMap,
            'block_questions' => array_values($blockQuestionNumbers),
        ];
    }

    protected function buildPlayerProgress($dinamica, ?DinamicaRegistro $registro, array $meta): array
    {
        $blocksMeta = array_values($meta['blocks'] ?? []);
        $blockQuestionNumbers = $meta['block_questions'] ?? [];
        $categoria = $meta['categoria'] ?? 'Bloque 1';
        $totalQuestions = (int) ($meta['cantidadCasillas'] ?? 0);

        if (empty($blocksMeta)) {
            $blocksMeta[] = [
                'title' => $categoria,
                'categoryName' => $categoria,
            ];
        }

        $normalizedBlocks = [];
        foreach ($blocksMeta as $index => $block) {
            $questionNumbers = $blockQuestionNumbers[$index] ?? [];
            if (empty($questionNumbers) && $totalQuestions > 0 && count($blocksMeta) === 1) {
                $questionNumbers = range(1, $totalQuestions);
            }

            $normalizedBlocks[] = [
                'index' => $index,
                'title' => $block['title'] ?? ('Bloque ' . ($index + 1)),
                'categoryName' => $block['categoryName'] ?? null,
                'questionNumbers' => $questionNumbers,
                'totalQuestions' => count($questionNumbers),
            ];
        }

        $answers = collect();
        if ($registro) {
            $answers = TriviaUserAnswer::where('dinamica_registro_id', $registro->id)
                ->select('numero_pregunta', 'puntos_obtenidos')
                ->get();
        }

        $answeredNumbers = $answers->pluck('numero_pregunta')
            ->map(fn ($value) => (int) $value)
            ->filter(fn ($value) => $value > 0)
            ->unique()
            ->sort()
            ->values()
            ->all();

        $scoreByQuestion = $answers->groupBy('numero_pregunta')
            ->map(fn ($rows) => (float) $rows->sum('puntos_obtenidos'));

        $progressBlocks = [];
        foreach ($normalizedBlocks as $block) {
            $questionNumbers = $block['questionNumbers'];
            $answeredInBlock = $questionNumbers
                ? array_values(array_intersect($questionNumbers, $answeredNumbers))
                : [];
            $score = 0.0;
            foreach ($answeredInBlock as $numero) {
                $score += $scoreByQuestion[$numero] ?? 0.0;
            }

            $progressBlocks[] = [
                'index' => $block['index'],
                'title' => $block['title'],
                'categoryName' => $block['categoryName'],
                'questionNumbers' => $questionNumbers,
                'totalQuestions' => $block['totalQuestions'],
                'answeredCount' => count($answeredInBlock),
                'completed' => $block['totalQuestions'] > 0
                    ? count($answeredInBlock) >= $block['totalQuestions']
                    : false,
                'score' => round($score, 2),
            ];
        }

        $nextPlayableIndex = 0;
        foreach ($progressBlocks as $progress) {
            if ($progress['completed']) {
                $nextPlayableIndex++;
            } else {
                break;
            }
        }
        $nextPlayableIndex = min($nextPlayableIndex, count($progressBlocks));
        $lastCompletedIndex = $nextPlayableIndex > 0 ? $nextPlayableIndex - 1 : null;
        $totalScore = round((float) $answers->sum('puntos_obtenidos'), 2);

        return [
            'blocks' => $progressBlocks,
            'answeredNumbers' => $answeredNumbers,
            'totalScore' => $totalScore,
            'nextPlayableIndex' => $nextPlayableIndex,
            'lastCompletedIndex' => $lastCompletedIndex,
            'hasMultipleBlocks' => count($progressBlocks) > 1,
        ];
    }

    protected function buildBlockLineup(array $gameBlocks, array $triviaConfig = []): array
    {
        $normalized = $this->normalizeGameBlocks($gameBlocks);

        $lineup = collect($normalized)
            ->filter(fn ($block) => ($block['isActive'] ?? true) && !empty($block['categoryId']))
            ->sortBy(function ($block, $index) {
                return $block['order'] ?? ($index + 1);
            })
            ->values()
            ->map(function ($block, $index) {
                return array_merge($block, [
                    'order' => $block['order'] ?? ($index + 1),
                    'title' => $block['title'] ?? ('Bloque ' . ($index + 1)),
                ]);
            })
            ->all();

        if (empty($lineup) && !empty($triviaConfig['categoryId'])) {
            $lineup[] = [
                'title' => $triviaConfig['categoria'] ?? 'Bloque 1',
                'categoryId' => (int) $triviaConfig['categoryId'],
                'order' => 1,
                'isActive' => true,
            ];
        }

        return $lineup;
    }

    protected function buildPointDistribution($preguntas, float $puntajeTotal, array $triviaConfig): array
    {
        $cantidad = $preguntas->count();
        if ($cantidad === 0 || $puntajeTotal <= 0) {
            return [];
        }

        $step = max(0.5, (float) ($triviaConfig['pointsStep'] ?? 0.5));
        $minPoints = max($step, (float) ($triviaConfig['minQuestionPoints'] ?? $step));
        $maxPoints = max($minPoints, (float) ($triviaConfig['maxQuestionPoints'] ?? $puntajeTotal));

        $remaining = $puntajeTotal;
        $puntajes = [];
        $preguntasArray = $preguntas->values();

        for ($index = 0; $index < $cantidad; $index++) {
            $restantes = $cantidad - $index - 1;

            if ($restantes === 0) {
                $value = round($remaining / $step) * $step;
                $puntajes[$index] = max($minPoints, min($maxPoints, $value));
                break;
            }

            $minDisponible = max($minPoints, $remaining - ($maxPoints * $restantes));
            $maxDisponible = min($maxPoints, $remaining - ($minPoints * $restantes));

            if ($minDisponible > $maxDisponible) {
                $minDisponible = $maxDisponible;
            }

            $pregunta = $preguntasArray[$index];
            $seedBase = $pregunta->id . '|' . $puntajeTotal . '|' . $cantidad . '|' . $index;
            $seed = crc32($seedBase);
            $ratio = (($seed % 1000) / 1000) ?: 0.5;
            $value = $minDisponible + ($maxDisponible - $minDisponible) * $ratio;
            $value = round($value / $step) * $step;
            $value = max($minPoints, min($maxPoints, $value));

            $puntajes[$index] = $value;
            $remaining = max(0, $remaining - $value);
        }

        $puntajes = $this->forcePointSum($puntajes, $puntajeTotal, $step, $minPoints, $maxPoints);

        return $puntajes;
    }

    protected function forcePointSum(array $puntajes, float $targetTotal, float $step, float $minPoints, float $maxPoints): array
    {
        $currentSum = array_sum($puntajes);
        $difference = round($targetTotal - $currentSum, 4);

        if (abs($difference) < 0.001) {
            return $puntajes;
        }

        $stepUnits = (int) round($difference / $step);
        $count = count($puntajes);

        if ($stepUnits === 0 && $difference !== 0.0 && $count) {
            $puntajes[$count - 1] = max($minPoints, min($maxPoints, $puntajes[$count - 1] + $difference));
            return $puntajes;
        }

        $direction = $stepUnits > 0 ? 1 : -1;
        $needed = abs($stepUnits);
        $index = 0;
        $safety = 0;
        $limit = $count * 10;

        while ($needed > 0 && $count > 0 && $safety < $limit) {
            $key = $index % $count;
            $current = $puntajes[$key];
            if ($direction > 0 && ($current + $step) <= $maxPoints) {
                $puntajes[$key] = round(($current + $step) / $step) * $step;
                $needed--;
            } elseif ($direction < 0 && ($current - $step) >= $minPoints) {
                $puntajes[$key] = round(($current - $step) / $step) * $step;
                $needed--;
            }
            $index++;
            $safety++;
        }

        if ($needed > 0 && $count > 0) {
            $puntajes[$count - 1] = max($minPoints, min($maxPoints, $puntajes[$count - 1] + ($direction * $needed * $step)));
        }

        return $puntajes;
    }

    protected function extractCategoryIdsFromBlocks(array $gameBlocks): array
    {
        return collect($gameBlocks)
            ->filter(fn ($block) => ($block['isActive'] ?? true))
            ->sortBy('order')
            ->pluck('categoryId')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    protected function fetchCategoryQuestions(int $categoryId): ?array
    {
        try {
            $categoriaModel = $this->questionCategoryService->findOrFail($categoryId);
        } catch (ModelNotFoundException $exception) {
            \Log::warning('Trivia preview: categoría no encontrada', [
                'category_id' => $categoryId,
                'message' => $exception->getMessage(),
            ]);
            return null;
        }

        $preguntasBase = $categoriaModel->questions()
            ->with('options')
            ->whereNull('deleted_at');

        $preguntas = (clone $preguntasBase)
            ->where('is_active', 1)
            ->where('status', 'active')
            ->get();

        if ($preguntas->isEmpty()) {
            $preguntas = $preguntasBase->get();
        }

        return [
            'nombre' => $categoriaModel->name,
            'preguntas' => $preguntas,
        ];
    }

    protected function buildLeaderboard($participantes, float $puntajeTotal, array $blocks = [], array $questionBlockMap = []): array
    {
        $participantIds = $participantes->pluck('id');

        $puntajes = TriviaUserAnswer::query()
            ->select(
                'dinamica_registro_id',
                DB::raw('SUM(puntos_obtenidos) as total_puntos'),
                DB::raw('SUM(tiempo_respuesta) as total_tiempo')
            )
            ->when($participantIds->isNotEmpty(), fn ($query) => $query->whereIn('dinamica_registro_id', $participantIds))
            ->groupBy('dinamica_registro_id')
            ->get()
            ->keyBy('dinamica_registro_id');

        $blockScores = [];
        if (!empty($blocks) && !empty($questionBlockMap) && $participantIds->isNotEmpty()) {
            $answersGrouped = TriviaUserAnswer::query()
                ->select(
                    'dinamica_registro_id',
                    'question_item_id',
                    DB::raw('SUM(puntos_obtenidos) as total_puntos')
                )
                ->whereIn('dinamica_registro_id', $participantIds)
                ->groupBy('dinamica_registro_id', 'question_item_id')
                ->get();

            foreach ($answersGrouped as $row) {
                $blockIndex = $questionBlockMap[$row->question_item_id] ?? null;
                if ($blockIndex === null) {
                    continue;
                }

                $current = $blockScores[$row->dinamica_registro_id][$blockIndex] ?? 0.0;
                $blockScores[$row->dinamica_registro_id][$blockIndex] = $current + (float) $row->total_puntos;
            }
        }

        return $participantes->values()->map(function (DinamicaRegistro $registro) use ($puntajes, $puntajeTotal, $blocks, $blockScores) {
            $totales = $puntajes[$registro->id] ?? null;
            $puntaje = $totales ? (float) $totales->total_puntos : 0.0;
            $tiempoTotal = $totales ? (float) $totales->total_tiempo : 0.0;

            if ($registro->ha_ganado && $puntaje < $puntajeTotal) {
                $puntaje = $puntajeTotal;
            }

            return [
                'id' => $registro->id,
                'nombre' => $registro->nombre,
                'apellido' => $registro->apellido,
                'email' => $registro->email,
                'haGanado' => (bool) $registro->ha_ganado,
                'haJugado' => (bool) $registro->ha_jugado,
                'turno' => $registro->turno,
                'puntaje' => round($puntaje, 2),
                'tiempoTotal' => round($tiempoTotal, 2),
                'blocks' => $this->formatParticipantBlocks($blocks, $blockScores[$registro->id] ?? []),
            ];
        })->sort(function (array $a, array $b) {
            if ($a['puntaje'] === $b['puntaje']) {
                return $a['tiempoTotal'] <=> $b['tiempoTotal'];
            }
            return $b['puntaje'] <=> $a['puntaje'];
        })->values()->toArray();
    }

    protected function formatParticipantBlocks(array $blocks, array $scores): array
    {
        if (empty($blocks)) {
            return [];
        }

        $formatted = [];
        foreach (array_values($blocks) as $index => $block) {
            $formatted[] = [
                'index' => $index,
                'title' => $block['title'] ?? ('Bloque ' . ($index + 1)),
                'categoryId' => $block['categoryId'] ?? null,
                'categoryName' => $block['categoryName'] ?? null,
                'puntaje' => round($scores[$index] ?? 0.0, 2),
            ];
        }

        return $formatted;
    }
}
