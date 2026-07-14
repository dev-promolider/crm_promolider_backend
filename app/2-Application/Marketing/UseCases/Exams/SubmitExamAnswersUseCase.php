<?php

namespace Promolider\Application\Marketing\UseCases\Exams;

use Promolider\Domain\Marketing\Ports\Out\ExamRepositoryInterface;

class SubmitExamAnswersUseCase
{
    public function __construct(
        private ExamRepositoryInterface $examRepository
    ) {}

    /**
     * Procesa las respuestas del examen, las califica y guarda los resultados.
     *
     * @param int   $examId    ID del examen
     * @param int   $userId    ID del usuario
     * @param array $answers   Respuestas del usuario [{option: mixed}, ...]
     * @param int   $courseId  ID del curso asociado
     *
     * @return array { rate, condition, message, points_gained }
     */
    public function execute(int $examId, int $userId, array $answers, int $courseId): array
    {
        $exam = $this->examRepository->findExamById($examId);
        if (!$exam) {
            throw new \RuntimeException('Examen no encontrado', 404);
        }

        $questions = $this->examRepository->getExamQuestions($examId);
        if (empty($questions)) {
            throw new \RuntimeException('El examen no tiene preguntas', 400);
        }

        // 1. Crear header de examen
        $productorId = (int) ($exam['productor_id'] ?? 0);
        $headerId = $this->examRepository->createUserExamHeader($userId, $productorId, $examId);

        // 2. Calificar cada pregunta
        $totalRate = 0;
        foreach ($questions as $index => $question) {
            $userOption = $answers[$index]['option'] ?? null;
            $questionTypeId = (int) ($question['question_type_id'] ?? $question['type'] ?? 1);
            $points = (float) ($question['points'] ?? 0);

            $rate = $this->evaluateQuestion($question, $userOption, $questionTypeId, $headerId);
            $totalRate += $rate;
        }

        // 3. Determinar condición
        $minScore = (int) ($exam['min_passing_score'] ?? 0);
        $hasOpenQuestions = $this->hasOpenQuestions($questions);

        if ($hasOpenQuestions) {
            // Si hay preguntas abiertas, el productor debe calificar
            $condition = 'Waiting';
            $status = false;
        } else {
            $condition = $totalRate >= $minScore ? 'Approved' : 'Disapproved';
            $status = true;

            // Guardar en user_exam si no hay preguntas abiertas
            $examTypeId = $this->determineExamType($exam);
            $this->examRepository->saveUserExam($courseId, $userId, $examTypeId, $totalRate);
        }

        // 4. Actualizar header
        $this->examRepository->updateUserExamHeader($headerId, $totalRate, $condition, $status);

        $pointsGained = $totalRate; // Puntos ganados = nota del examen

        return [
            'rate' => $totalRate,
            'condition' => $condition,
            'message' => $hasOpenQuestions ? 'Examen enviado, esperando calificación del productor' : 'Examen calificado',
            'points_gained' => $pointsGained,
        ];
    }

    /**
     * Evalúa una pregunta según su tipo.
     */
    private function evaluateQuestion(array $question, $userOption, int $questionTypeId, int $headerId): float
    {
        $points = (float) ($question['points'] ?? 0);

        switch ($questionTypeId) {
            case 1: // Selección simple
                $correct = (string) ($question['correct'] ?? '');
                $isCorrect = (string) $userOption === $correct;
                $rate = $isCorrect ? $points : 0;
                $this->examRepository->saveUserAnswer($headerId, $rate, (string) $userOption);
                return $rate;

            case 2: // Opción múltiple
                $correctOptions = explode(',', (string) ($question['correct'] ?? ''));
                $userOptions = is_array($userOption) ? $userOption : [$userOption];
                $options = $question['options'] ?? [];

                $correctCount = count(array_intersect($correctOptions, $userOptions));
                $incorrectOptions = $this->getIncorrectOptions($options, $correctOptions);
                $incorrectCount = count(array_intersect($incorrectOptions, $userOptions));

                $pointsPerCorrect = $points / max(count($correctOptions), 1);
                $pointsPerIncorrect = $points / max(count($options), 1);

                $positivePoints = $correctCount * $pointsPerCorrect;
                $negativePoints = $incorrectCount * $pointsPerIncorrect;
                $finalRate = max(0, $positivePoints - $negativePoints);

                $this->examRepository->saveUserAnswer($headerId, $finalRate, $userOptions);
                return $finalRate;

            case 3: // Verdadero/Falso (booleano)
                $correct = (string) ($question['correct'] ?? '');
                $isCorrect = (string) $userOption === $correct;
                $rate = $isCorrect ? $points : 0;
                $this->examRepository->saveUserAnswer($headerId, $rate, (string) $userOption);
                return $rate;

            case 4: // Pregunta abierta
                $this->examRepository->saveUserAnswer($headerId, 0, $userOption);
                return 0;

            default:
                return 0;
        }
    }

    private function hasOpenQuestions(array $questions): bool
    {
        foreach ($questions as $q) {
            $type = (int) ($q['question_type_id'] ?? $q['type'] ?? 1);
            if ($type === 4) {
                return true;
            }
        }
        return false;
    }

    private function determineExamType(array $exam): int
    {
        if (!empty($exam['course_id'])) return 1;
        if (!empty($exam['module_id'])) return 2;
        if (!empty($exam['lesson_id'])) return 3;
        return 1;
    }

    private function getIncorrectOptions(array $options, array $correctOptions): array
    {
        $incorrect = [];
        foreach ($options as $index => $item) {
            if (!in_array((string)$index, $correctOptions)) {
                $incorrect[] = (string)$index;
            }
        }
        return $incorrect;
    }
}
