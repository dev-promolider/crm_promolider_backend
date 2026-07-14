<?php

namespace Promolider\Domain\Marketing\Ports\Out;

interface ReportRepositoryInterface
{
    public function getContentReport(string $type, string $view, ?int $userId = null): array;

    public function getPrivateContentReport(): array;

    public function getPrivateContentStudents(string $contentType, int $contentId): array;

    public function getContentByStatus(): array;

    public function getContentByProducer(): array;

    public function getGeneralReports(): array;

    public function getMasterclassReportByAdmin(): array;

    public function getMiniCourseReportByAdmin(): array;

    public function getEbookReportByAdmin(): array;

    public function getProducerReport(string $type, int $producerId): array;

    public function getDistributorReport(string $type, int $distributorId): array;

    /** @return array */
    public function getDistributors(string $type, int $contentId): array;

    /** @return array */
    public function getStudents(string $type, int $contentId): array;

    /** @return array */
    public function getPendingParticipants(string $type, int $contentId): array;

    /**
     * Obtiene todos los estudiantes (masterclass, minicourse, ebook) de un distribuidor.
     * @return array
     */
    public function getAllStudentsList(int $userId): array;

    /**
     * Obtiene todos los participantes filtrados por isParticipant para un usuario.
     * @return array
     */
    public function getAllParticipantsByUser(int $userId, ?int $isParticipant = null): array;

    /**
     * Obtiene las ultimas N ventas de cursos comprados por un usuario.
     * @return array
     */
    public function getLastSells(int $userId, int $limit = 5): array;
}
