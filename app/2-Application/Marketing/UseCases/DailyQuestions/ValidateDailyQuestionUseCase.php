<?php

namespace Promolider\Application\Marketing\UseCases\DailyQuestions;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserDailyQuizz;
use App\Models\UserClassroomPoint;
use App\Models\ClassroomPointDetail;
use App\Models\Option;
use App\Models\Badge;
use App\Models\BadgeDetail;

class ValidateDailyQuestionUseCase
{
    const CACHE_PREFIX = 'daily_question_';

    /**
     * Valida la respuesta del usuario a la pregunta diaria.
     *
     * @param int    $userId
     * @param string $userAnswer
     *
     * @return array { earned_points, total_points, correct, message }
     */
    public function execute(int $userId, string $userAnswer): array
    {
        // Verificar si ya respondió hoy
        $user = User::find($userId);
        if (!$user || $user->daily_quizz_status) {
            throw new \RuntimeException('Already answered today', 400);
        }

        // Obtener la respuesta correcta almacenada en caché
        $cacheKey = self::CACHE_PREFIX . $userId . '_' . date('Y-m-d');
        $storedQuestion = Cache::get($cacheKey);

        if (!$storedQuestion) {
            throw new \RuntimeException('Question expired or not found', 404);
        }

        $actualPoints = UserClassroomPoint::where('id_user', $userId)->first()->total_points ?? 0;

        try {
            DB::beginTransaction();

            // Marcar como respondida
            User::where('id', $userId)->update(['daily_quizz_status' => true]);

            // Validar la respuesta
            $isCorrect = strtolower(trim($userAnswer)) === strtolower(trim($storedQuestion['correct_answer']));

            // Limpiar caché
            Cache::forget($cacheKey);

            if ($isCorrect) {
                // Obtener puntos configurados
                $pointsOption = Option::where('description', 'daily_question')->first();
                $points = $pointsOption ? (int) $pointsOption->value : 10;

                // Guardar detalle de puntos
                $this->storeDetailPoints($userId, $points);
                $actualPoints += $points;

                // Incrementar contador daily
                $quizz = UserDailyQuizz::where('id_user', $userId)->first();
                if ($quizz) {
                    $quizz->increment('passed_quizz');
                }

                // Verificar logros (badges)
                $this->checkDailyQuestionBadges($userId);

                DB::commit();

                return [
                    'earned_points' => $points,
                    'total_points' => $actualPoints,
                    'correct' => true,
                    'message' => 'Respuesta correcta',
                ];
            } else {
                DB::commit();

                return [
                    'earned_points' => 0,
                    'total_points' => $actualPoints,
                    'correct' => false,
                    'message' => 'Respuesta incorrecta',
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function storeDetailPoints(int $userId, int $points): void
    {
        $header = UserClassroomPoint::where('id_user', $userId)->first();

        if (!$header) {
            $header = new UserClassroomPoint();
            $header->id_user = $userId;
            $header->total_points = 0;
            $header->save();
        }

        $detail = new ClassroomPointDetail();
        $detail->id_user_classroom_points = $header->id;
        $detail->increment_points = $points;
        $detail->description = 'Pregunta diaria';
        $detail->save();

        // Actualizar total
        $header->increment('total_points', $points);
    }

    private function checkDailyQuestionBadges(int $userId): void
    {
        $badgeLevels = [
            13 => 1,   // badge_id => condition (veces requeridas)
            14 => 10,
            15 => 25,
        ];

        $passedQuizz = UserDailyQuizz::where('id_user', $userId)->first();
        $countPassed = $passedQuizz ? (int) $passedQuizz->passed_quizz : 0;

        foreach ($badgeLevels as $badgeId => $goal) {
            $badge = Badge::find($badgeId);
            if (!$badge) continue;

            $userHasBadge = BadgeDetail::where('user_id', $userId)
                ->where('badge_id', $badgeId)
                ->exists();

            if (!$userHasBadge && $countPassed >= $goal) {
                $badgeDetail = new BadgeDetail();
                $badgeDetail->user_id = $userId;
                $badgeDetail->badge_id = $badgeId;
                $badgeDetail->save();
            }
        }
    }
}
