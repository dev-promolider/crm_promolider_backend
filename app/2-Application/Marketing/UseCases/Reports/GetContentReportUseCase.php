<?php

namespace Promolider\Application\Marketing\UseCases\Reports;

use Promolider\Domain\Marketing\Ports\Out\ReportRepositoryInterface;

class GetContentReportUseCase
{
    public function __construct(
        private readonly ReportRepositoryInterface $reportRepository
    ) {}

    public function execute(string $type, string $view, ?int $userId = null): array
    {
        return $this->reportRepository->getContentReport($type, $view, $userId);
    }

    public function getMasterclassReportByAdmin(): array
    {
        return $this->reportRepository->getMasterclassReportByAdmin();
    }

    public function getMiniCourseReportByAdmin(): array
    {
        return $this->reportRepository->getMiniCourseReportByAdmin();
    }

    public function getEbookReportByAdmin(): array
    {
        return $this->reportRepository->getEbookReportByAdmin();
    }

    public function getProducerReport(string $type, int $producerId): array
    {
        return $this->reportRepository->getProducerReport($type, $producerId);
    }

    public function getDistributorReport(string $type, int $distributorId): array
    {
        return $this->reportRepository->getDistributorReport($type, $distributorId);
    }
}
