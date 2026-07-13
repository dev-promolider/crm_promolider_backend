<?php

namespace Promolider\Infrastructure\Marketing\In\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Promolider\Application\Marketing\UseCases\DailyQuestions\GetDailyQuestionUseCase;
use Promolider\Application\Marketing\UseCases\DailyQuestions\ValidateDailyQuestionUseCase;

class DailyQuestionsController extends Controller
{
    public function __construct(
        private GetDailyQuestionUseCase $getDailyQuestionUseCase,
        private ValidateDailyQuestionUseCase $validateDailyQuestionUseCase,
    ) {}

    /**
     * GET /marketing/courses/daily-question
     * Obtiene la pregunta diaria para el usuario autenticado.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $result = $this->getDailyQuestionUseCase->execute(
                $user->id,
                (bool) $user->daily_quizz_status
            );

            return response()->json($result);
        } catch (\RuntimeException $e) {
            $status = $e->getCode() >= 100 && $e->getCode() <= 599 ? $e->getCode() : 500;
            return response()->json(['message' => $e->getMessage()], $status);
        }
    }

    /**
     * POST /marketing/courses/daily-question/validate
     * Valida la respuesta del usuario a la pregunta diaria.
     */
    public function validate(Request $request)
    {
        try {
            $data = $request->validate([
                'userAnswer' => 'required|string',
                'questionId' => 'nullable|string',
            ]);

            $user = $request->user();

            $result = $this->validateDailyQuestionUseCase->execute(
                $user->id,
                $data['userAnswer']
            );

            return response()->json($result);
        } catch (\RuntimeException $e) {
            $status = $e->getCode() >= 100 && $e->getCode() <= 599 ? $e->getCode() : 400;
            return response()->json(['message' => $e->getMessage()], $status);
        }
    }
}
