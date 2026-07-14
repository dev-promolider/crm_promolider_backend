<?php

namespace Promolider\Application\Marketing\UseCases\Reports;

use Promolider\Domain\Marketing\Ports\Out\ReportRepositoryInterface;

class GetStudentsReportUseCase
{
    public function __construct(
        private ReportRepositoryInterface $reportRepository
    ) {}

    public function getDistributors(string $type, int $contentId): array
    {
        return $this->reportRepository->getDistributors($type, $contentId);
    }

    public function getStudents(string $type, int $contentId): array
    {
        return $this->reportRepository->getStudents($type, $contentId);
    }

    public function getPendingParticipants(string $type, int $contentId): array
    {
        return $this->reportRepository->getPendingParticipants($type, $contentId);
    }

    public function getPrivateContentStudents(string $contentType, int $contentId): array
    {
        return $this->reportRepository->getPrivateContentStudents($contentType, $contentId);
    }

    public function getAllStudentsList(int $userId): array
    {
        return $this->reportRepository->getAllStudentsList($userId);
    }

    public function getAllParticipantsByUser(int $userId, ?int $isParticipant = null): array
    {
        return $this->reportRepository->getAllParticipantsByUser($userId, $isParticipant);
    }

    public function getLastSells(int $userId, int $limit = 5): array
    {
        return $this->reportRepository->getLastSells($userId, $limit);
    }
}
