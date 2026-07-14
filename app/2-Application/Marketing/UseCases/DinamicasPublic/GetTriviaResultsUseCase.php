<?php

namespace Promolider\Application\Marketing\UseCases\DinamicasPublic;

use Promolider\Domain\Marketing\Ports\Out\DinamicaRepositoryInterface;

class GetTriviaResultsUseCase
{
    public function __construct(
        private DinamicaRepositoryInterface $dinamicaRepository
    ) {}

    public function execute(string $slug): array
    {
        $dinamica = $this->dinamicaRepository->findBySlug($slug);

        if (!$dinamica || !$dinamica->isTrivia()) {
            throw new \RuntimeException('Trivia no encontrada', 404);
        }

        $config = $this->dinamicaRepository->getTriviaConfig($dinamica->id);
        $triviaCfg = $config['trivia_config'] ?? [];
        $gameBlocks = $config['game_blocks'] ?? [];
        $puntajeTotal = (float) ($triviaCfg['pointsMax'] ?? 20);

        // Construir info de bloques
        $blocks = [];
        foreach ($gameBlocks as $index => $block) {
            $blocks[] = [
                'index' => $index,
                'title' => $block['title'] ?? ('Bloque ' . ($index + 1)),
                'categoryId' => $block['categoryId'] ?? null,
            ];
        }

        $leaderboard = $this->dinamicaRepository->getLeaderboard($dinamica->id);

        // Encontrar ganador (primer lugar o quien tenga ha_ganado)
        $winner = null;
        foreach ($leaderboard as $p) {
            if ($p['ha_ganado'] || $p['puntaje'] >= $puntajeTotal) {
                $winner = $p;
                break;
            }
        }
        if (!$winner && !empty($leaderboard)) {
            $winner = $leaderboard[0];
        }

        return [
            'dinamica_nombre' => $dinamica->nombre,
            'puntaje_total' => $puntajeTotal,
            'blocks' => $blocks,
            'leaderboard' => $leaderboard,
            'winner' => $winner,
            'total_participantes' => count($leaderboard),
        ];
    }
}
