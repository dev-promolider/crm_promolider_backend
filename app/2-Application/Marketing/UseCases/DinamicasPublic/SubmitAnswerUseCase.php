<?php

namespace Promolider\Application\Marketing\UseCases\DinamicasPublic;

use Promolider\Domain\Marketing\Ports\Out\DinamicaRepositoryInterface;

class SubmitAnswerUseCase
{
    public function __construct(
        private DinamicaRepositoryInterface $dinamicaRepository
    ) {}

    public function execute(string $slug, int $numero, string $email, array $data): array
    {
        $dinamica = $this->dinamicaRepository->findBySlug($slug);

        if (!$dinamica || !$dinamica->isTrivia()) {
            throw new \RuntimeException('Trivia no encontrada', 404);
        }

        $registro = $this->dinamicaRepository->getRegistroByEmail($dinamica->id, $email);

        if (!$registro) {
            throw new \RuntimeException('No estás registrado en esta trivia', 403);
        }

        // Cargar preguntas
        $config = $this->dinamicaRepository->getTriviaConfig($dinamica->id);
        $gameBlocks = $config['game_blocks'] ?? [];
        $triviaCfg = $config['trivia_config'] ?? [];

        $allQuestions = [];
        foreach ($gameBlocks as $block) {
            $categoryId = $block['categoryId'] ?? null;
            if (!$categoryId) continue;
            $questions = $this->dinamicaRepository->getCategoryQuestions((int) $categoryId);
            $allQuestions = array_merge($allQuestions, $questions);
        }

        $indice = $numero - 1;
        if ($indice < 0 || $indice >= count($allQuestions)) {
            throw new \RuntimeException('Pregunta no encontrada', 404);
        }

        $question = $allQuestions[$indice];
        $puntajeTotal = (float) ($triviaCfg['pointsMax'] ?? 20);
        $timeout = (bool) ($data['timeout'] ?? false);
        $opcionIndex = $data['opcion_index'] ?? null;

        if (!$timeout && $opcionIndex === null) {
            throw new \RuntimeException('Selecciona una opción antes de enviar', 422);
        }

        $opciones = $question['options'] ?? [];
        $opcionElegida = (!$timeout && isset($opciones[$opcionIndex])) ? $opciones[$opcionIndex] : null;

        if (!$timeout && !$opcionElegida) {
            throw new \RuntimeException('La opción seleccionada no es válida', 422);
        }

        $esCorrecta = $timeout ? false : ($opcionElegida['is_correct'] ?? false);

        // Calcular puntaje
        $tiempoLimite = (int) ($triviaCfg['questionTimeLimit'] ?? 30);
        $elapsedMs = max(0, (float) ($data['elapsed_ms'] ?? 0));
        $tiempoRespuesta = $timeout ? $tiempoLimite : min($tiempoLimite, round($elapsedMs / 1000, 2));

        // Distribuir puntaje
        $count = count($allQuestions);
        $step = max(0.5, (float) ($triviaCfg['pointsStep'] ?? 0.5));
        $minPoints = max($step, (float) ($triviaCfg['minQuestionPoints'] ?? $step));
        $maxPoints = max($minPoints, (float) ($triviaCfg['maxQuestionPoints'] ?? $puntajeTotal));

        // Calcular valor de esta pregunta
        $remainingQuestions = $count - $indice;
        $baseValue = $remainingQuestions > 1
            ? ($puntajeTotal / $count)
            : $puntajeTotal - array_sum(array_fill(0, $count - 1, $puntajeTotal / $count));
        $valorPregunta = max($minPoints, min($maxPoints, round($baseValue / $step) * $step));
        $puntosObtenidos = $esCorrecta ? $valorPregunta : 0.0;

        // Guardar respuesta
        $this->dinamicaRepository->saveTriviaAnswer($dinamica->id, $registro->id, [
            'numero_pregunta' => $numero,
            'question_item_id' => $question['id'],
            'opcion_indice' => $timeout ? null : $opcionIndex,
            'opcion_texto' => $opcionElegida['text'] ?? null,
            'es_correcta' => $esCorrecta,
            'valor_pregunta' => $valorPregunta,
            'puntos_obtenidos' => $puntosObtenidos,
            'tiempo_respuesta' => $tiempoRespuesta,
        ]);

        // Marcar como jugado si es primera respuesta
        if (!$registro->hasPlayed()) {
            $this->dinamicaRepository->markAsPlayed($registro->id);
        }

        // Calcular puntaje acumulado
        $respuestas = $this->dinamicaRepository->getTriviaAnswers($dinamica->id, $registro->id);
        $puntajeAcumulado = array_sum(array_column($respuestas, 'puntos_obtenidos'));
        $preguntasContestadas = count($respuestas);
        $completado = $preguntasContestadas >= $count;

        return [
            'success' => true,
            'correcta' => $esCorrecta,
            'puntaje_pregunta' => $valorPregunta,
            'puntos_obtenidos' => $puntosObtenidos,
            'puntaje_acumulado' => $puntajeAcumulado,
            'preguntas_contestadas' => $preguntasContestadas,
            'completado' => $completado,
            'timeout' => $timeout,
        ];
    }
}
