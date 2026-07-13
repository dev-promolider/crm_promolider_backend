<?php

namespace Promolider\Application\Marketing\UseCases\Exams;

use Promolider\Domain\Marketing\Ports\Out\ExamRepositoryInterface;

class GetActiveExamUseCase
{
    public function __construct(
        private ExamRepositoryInterface $examRepository
    ) {}

    /**
     * Obtiene el examen activo y verifica si el usuario puede realizarlo.
     *
     * @param string $examType 'course', 'module', o 'class'
     * @param int    $idType   ID del curso, módulo o clase
     * @param int    $userId   ID del usuario autenticado
     *
     * @return array { exam, can_take, message, attempts_count }
     *
     * @throws \RuntimeException si no hay examen activo
     */
    public function execute(string $examType, int $idType, int $userId): array
    {
        $exam = $this->examRepository->getActiveExam($examType, $idType);

        if (!$exam) {
            throw new \RuntimeException('No existe un examen activo para este contenido', 404);
        }

        $examId = (int) $exam['id'];

        // Verificar si el usuario ya aprobó
        if ($this->examRepository->userHasApproved($examId, $userId)) {
            return [
                'exam' => $exam,
                'can_take' => false,
                'message' => 'El usuario ya aprobó el examen',
                'attempts_count' => $this->examRepository->getUserAttemptsCount($examId, $userId),
            ];
        }

        // Verificar si tiene un intento en espera
        if ($this->examRepository->userHasWaitingAttempt($examId, $userId)) {
            return [
                'exam' => $exam,
                'can_take' => false,
                'message' => 'Examen en espera de calificación',
                'attempts_count' => $this->examRepository->getUserAttemptsCount($examId, $userId),
            ];
        }

        // Verificar límite de intentos (3 máximo como en el monolito)
        $attempts = $this->examRepository->getUserAttemptsCount($examId, $userId);
        if ($attempts >= 3) {
            return [
                'exam' => $exam,
                'can_take' => false,
                'message' => 'Límite de intentos alcanzado (3/3)',
                'attempts_count' => $attempts,
            ];
        }

        return [
            'exam' => $exam,
            'can_take' => true,
            'message' => 'Puedes realizar el examen',
            'attempts_count' => $attempts,
        ];
    }
}
