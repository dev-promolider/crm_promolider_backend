<?php

namespace Promolider\Application\Marketing\UseCases\DinamicasPublic;

use Promolider\Domain\Marketing\Ports\Out\DinamicaRepositoryInterface;

class GetTriviaPreviewUseCase
{
    public function __construct(
        private DinamicaRepositoryInterface $dinamicaRepository
    ) {}

    public function execute(string $slug, string $email): array
    {
        $dinamica = $this->dinamicaRepository->findBySlug($slug);

        if (!$dinamica || !$dinamica->isTrivia()) {
            throw new \RuntimeException('Trivia no encontrada', 404);
        }

        if (!$dinamica->isActive) {
            throw new \RuntimeException('La trivia no está activa', 403);
        }

        $registro = $this->dinamicaRepository->getRegistroByEmail($dinamica->id, $email);

        if (!$registro) {
            throw new \RuntimeException('No estás registrado en esta trivia', 403);
        }

        $config = $this->dinamicaRepository->getTriviaConfig($dinamica->id);
        $triviaCfg = $config['trivia_config'] ?? [];
        $gameBlocks = $config['game_blocks'] ?? [];

        $puntajeTotal = (float) ($triviaCfg['pointsMax'] ?? 20);
        $tiempoPregunta = (int) ($triviaCfg['questionTimeLimit'] ?? 30);

        // Cargar preguntas desde las categorías definidas en game_blocks
        $blocks = [];
        $allQuestions = [];
        $puntajes = [];
        $questionCounter = 0;

        foreach ($gameBlocks as $index => $block) {
            $categoryId = $block['categoryId'] ?? null;
            if (!$categoryId) continue;

            $questions = $this->dinamicaRepository->getCategoryQuestions((int) $categoryId);
            if (empty($questions)) continue;

            $questionNumbers = [];
            foreach ($questions as $q) {
                $questionCounter++;
                $allQuestions[] = $q;
                $questionNumbers[] = $questionCounter;
            }

            $blocks[] = [
                'index' => $index,
                'title' => $block['title'] ?? ('Bloque ' . ($index + 1)),
                'categoryId' => (int) $categoryId,
                'questionNumbers' => $questionNumbers,
                'totalQuestions' => count($questions),
            ];
        }

        $cantidadPreguntas = count($allQuestions);

        // Respuestas del usuario
        $respuestas = $registro
            ? $this->dinamicaRepository->getTriviaAnswers($dinamica->id, $registro->id)
            : [];

        $answeredNumbers = array_map(function ($r) {
            return (int) ($r['numero_pregunta'] ?? 0);
        }, $respuestas);

        $puntajeAcumulado = array_sum(array_column($respuestas, 'puntos_obtenidos'));

        // Progreso por bloque
        $blocksProgress = [];
        foreach ($blocks as $block) {
            $answeredInBlock = array_intersect($block['questionNumbers'], $answeredNumbers);
            $blocksProgress[] = [
                'index' => $block['index'],
                'title' => $block['title'],
                'questionNumbers' => $block['questionNumbers'],
                'totalQuestions' => $block['totalQuestions'],
                'answeredCount' => count($answeredInBlock),
                'completed' => count($answeredInBlock) >= $block['totalQuestions'],
            ];
        }

        return [
            'dinamica_nombre' => $dinamica->nombre,
            'dinamica_descripcion' => $dinamica->descripcion,
            'blocks' => $blocksProgress,
            'total_preguntas' => $cantidadPreguntas,
            'preguntas_respondidas' => count($answeredNumbers),
            'answered_numbers' => array_values($answeredNumbers),
            'puntaje_total' => $puntajeTotal,
            'puntaje_acumulado' => $puntajeAcumulado,
            'tiempo_pregunta' => $tiempoPregunta,
            'completado' => count($answeredNumbers) >= $cantidadPreguntas,
        ];
    }

    private function distributePoints(int $count, float $total, array $config): array
    {
        if ($count === 0) return [];

        $step = max(0.5, (float) ($config['pointsStep'] ?? 0.5));
        $minPoints = max($step, (float) ($config['minQuestionPoints'] ?? $step));
        $maxPoints = max($minPoints, (float) ($config['maxQuestionPoints'] ?? $total));

        $remaining = $total;
        $puntajes = [];

        for ($i = 0; $i < $count; $i++) {
            $restantes = $count - $i - 1;

            if ($restantes === 0) {
                $puntajes[$i] = round($remaining / $step) * $step;
                break;
            }

            $minDisp = max($minPoints, $remaining - ($maxPoints * $restantes));
            $maxDisp = min($maxPoints, $remaining - ($minPoints * $restantes));

            if ($minDisp > $maxDisp) $minDisp = $maxDisp;

            $ratio = abs(crc32((string) $i . (string) $total) % 1000) / 1000 ?: 0.5;
            $value = $minDisp + ($maxDisp - $minDisp) * $ratio;
            $value = round($value / $step) * $step;
            $value = max($minPoints, min($maxPoints, $value));

            $puntajes[$i] = $value;
            $remaining = max(0, $remaining - $value);
        }

        return $puntajes;
    }
}
