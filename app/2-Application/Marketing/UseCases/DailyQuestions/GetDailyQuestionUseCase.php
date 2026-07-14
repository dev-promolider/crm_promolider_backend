<?php

namespace Promolider\Application\Marketing\UseCases\DailyQuestions;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GetDailyQuestionUseCase
{
    const CACHE_PREFIX = 'daily_question_';
    const API_URL = 'https://the-trivia-api.com/api/questions?limit=1';

    /**
     * Obtiene la pregunta diaria para un usuario.
     * Si ya respondió hoy, retorna mensaje.
     *
     * @param int  $userId
     * @param bool $hasAnsweredToday Si el usuario ya respondió hoy
     *
     * @return array
     */
    public function execute(int $userId, bool $hasAnsweredToday): array
    {
        if ($hasAnsweredToday) {
            return ['message' => 'try again tomorrow'];
        }

        $response = Http::timeout(10)->get(self::API_URL);

        if (!$response->successful()) {
            throw new \RuntimeException('No se pudo obtener la pregunta diaria', 502);
        }

        $data = $response->json();

        if (empty($data) || !isset($data[0])) {
            throw new \RuntimeException('No se pudo obtener la pregunta diaria', 502);
        }

        $questionData = $data[0];

        // Almacenar la respuesta correcta en caché por 24 horas
        $cacheKey = self::CACHE_PREFIX . $userId . '_' . date('Y-m-d');
        Cache::put($cacheKey, [
            'question_id' => $questionData['id'] ?? uniqid(),
            'correct_answer' => $questionData['correctAnswer'],
        ], now()->addHours(24));

        // Retornar sin la respuesta correcta
        unset($questionData['correctAnswer']);
        $questionData['correctAnswer'] = null;

        return $questionData;
    }
}
