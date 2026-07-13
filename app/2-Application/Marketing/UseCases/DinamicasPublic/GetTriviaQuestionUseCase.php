<?php

namespace Promolider\Application\Marketing\UseCases\DinamicasPublic;

use Promolider\Domain\Marketing\Ports\Out\DinamicaRepositoryInterface;

class GetTriviaQuestionUseCase
{
    public function __construct(
        private DinamicaRepositoryInterface $dinamicaRepository
    ) {}

    public function execute(string $slug, int $numero, string $email): array
    {
        $dinamica = $this->dinamicaRepository->findBySlug($slug);

        if (!$dinamica || !$dinamica->isTrivia()) {
            throw new \RuntimeException('Trivia no encontrada', 404);
        }

        $registro = $this->dinamicaRepository->getRegistroByEmail($dinamica->id, $email);

        if (!$registro) {
            throw new \RuntimeException('No estás registrado en esta trivia', 403);
        }

        // Cargar todas las preguntas de la trivia (desde game_blocks)
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
        $tiempoPregunta = (int) ($triviaCfg['questionTimeLimit'] ?? 30);

        $opciones = array_map(function ($opt) {
            return [
                'id' => $opt['id'],
                'label' => $opt['label'],
                'text' => $opt['text'],
                'position' => $opt['position'],
            ];
        }, $question['options'] ?? []);

        return [
            'numero' => $numero,
            'total' => count($allQuestions),
            'pregunta' => $question['body'] ?? $question['title'] ?? '',
            'opciones' => $opciones,
            'tiempo_limite' => $tiempoPregunta,
            'puntaje_total' => $puntajeTotal,
        ];
    }
}
