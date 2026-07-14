<?php

namespace Promolider\Application\Marketing\UseCases\Exams;

use Promolider\Domain\Marketing\Ports\Out\ExamRepositoryInterface;

class GetExamResultsUseCase
{
    public function __construct(
        private ExamRepositoryInterface $examRepository
    ) {}

    /**
     * Obtiene los resultados del último intento del usuario en un examen.
     *
     * @param int $examId
     * @param int $userId
     * @return array { result: bool, detail: array }
     */
    public function execute(int $examId, int $userId): array
    {
        $questions = $this->examRepository->getExamQuestions($examId);
        $latestHeader = $this->examRepository->getLatestUserExamHeader($examId, $userId);

        if (!$latestHeader) {
            throw new \RuntimeException('No se encontraron intentos de examen para este usuario', 404);
        }

        $approved = ($latestHeader['condition'] ?? '') === 'Approved';
        $userAnswers = $this->examRepository->getUserAnswers((int) $latestHeader['id']);

        $detail = [];
        foreach ($questions as $index => $question) {
            $options = $question['options'] ?? [];
            $userSelectedIdx = $userAnswers[$index]['options_selected'] ?? null;
            $correctIdx = $question['correct'] ?? null;

            $userSelected = is_array($userSelectedIdx)
                ? $userSelectedIdx
                : ($options[$userSelectedIdx] ?? null);

            $correctAnswer = is_string($correctIdx)
                ? ($options[$correctIdx] ?? null)
                : null;

            $detail[] = [
                'pregunta' => $question['title'] ?? '',
                'respuestaSeleccionada' => $userSelected,
                'respuestaCorrecta' => $correctAnswer,
                'points' => $question['points'] ?? 0,
                'points_gained' => $userAnswers[$index]['points_gained'] ?? 0,
            ];
        }

        return [
            'result' => $approved,
            'condition' => $latestHeader['condition'] ?? '',
            'rate' => $latestHeader['rate'] ?? 0,
            'detail' => $detail,
        ];
    }
}
