<?php

namespace Promolider\Domain\Marketing\Ports\Out;

interface ExamRepositoryInterface
{
    /**
     * Obtener el examen activo para un curso/módulo/clase.
     */
    public function getActiveExam(string $examType, int $idType): ?array;

    /**
     * Obtener preguntas de un examen.
     */
    public function getExamQuestions(int $examId): array;

    /**
     * Obtener un examen por ID.
     */
    public function findExamById(int $examId): ?array;

    /**
     * Verificar si el usuario ya aprobó el examen.
     */
    public function userHasApproved(int $examId, int $userId): bool;

    /**
     * Obtener cantidad de intentos del usuario para un examen.
     */
    public function getUserAttemptsCount(int $examId, int $userId): int;

    /**
     * Verificar si el usuario tiene un intento en estado "Waiting".
     */
    public function userHasWaitingAttempt(int $examId, int $userId): bool;

    /**
     * Crear un nuevo header de examen para el usuario.
     */
    public function createUserExamHeader(int $userId, int $productorId, int $examId): int;

    /**
     * Guardar respuesta de usuario a una pregunta.
     */
    public function saveUserAnswer(int $userExamId, float $pointsGained, $optionsSelected): void;

    /**
     * Actualizar header de examen (rate, condition, status).
     */
    public function updateUserExamHeader(int $headerId, float $rate, string $condition, bool $status): void;

    /**
     * Obtener el último header de examen para un usuario y examen.
     */
    public function getLatestUserExamHeader(int $examId, int $userId): ?array;

    /**
     * Obtener respuestas del usuario para un header.
     */
    public function getUserAnswers(int $userExamId): array;

    /**
     * Guardar registro en user_exam.
     */
    public function saveUserExam(int $courseId, int $userId, int $examTypeId, float $note): void;

    /**
     * Obtener calificación existente de un usuario para una lección.
     */
    public function getCalificationByLesson(int $lessonId, int $userId): ?array;

    /**
     * Obtener el productor de un examen.
     */
    public function getExamProducer(int $examId): ?array;

    /**
     * Obtener la puntuación mínima para aprobar un examen.
     */
    public function getMinPassingScore(int $examId): int;
}
