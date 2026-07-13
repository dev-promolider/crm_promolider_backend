<?php

namespace Promolider\Domain\Marketing\Ports\Out;

interface PurchasedCourseRepositoryInterface
{
    /**
     * Obtener un curso comprado por usuario y curso.
     */
    public function findByUserAndCourse(int $userId, int $courseId): ?array;

    /**
     * Crear un nuevo registro de curso comprado.
     */
    public function create(int $userId, int $courseId, array $classesStatus): array;

    /**
     * Actualizar el estado de visualización de una clase.
     */
    public function updateClassStatus(int $userId, int $courseId, int $classId, string $status): void;

    /**
     * Guardar tiempo de reproducción y última clase vista.
     */
    public function saveClassSeen(int $userId, int $courseId, int $classId, ?string $displayTime): void;

    /**
     * Obtener el tiempo guardado para una clase específica.
     */
    public function getClassTime(int $userId, int $courseId, int $classId): ?array;

    /**
     * Obtener la última clase reproducida.
     */
    public function getLastClassPlayed(int $userId, int $courseId): ?array;

    /**
     * Obtener los cursos comprados y completados por un usuario para certificados.
     */
    public function getCompletedCourses(int $userId): array;

    /**
     * Obtener el estado de clases para un curso comprado.
     */
    public function getClassesStatus(int $userId, int $courseId): ?array;
}
