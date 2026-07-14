<?php

namespace Promolider\Application\Marketing\UseCases\Reports;

use Promolider\Domain\Marketing\Ports\Out\ReportRepositoryInterface;

class GetPrivateContentReportUseCase
{
    public function __construct(
        private readonly ReportRepositoryInterface $reportRepository
    ) {}

    public function getAll(): array
    {
        return $this->reportRepository->getPrivateContentReport();
    }

    public function getContentByStatus(): array
    {
        return $this->reportRepository->getContentByStatus();
    }

    public function getContentByProducer(): array
    {
        return $this->reportRepository->getContentByProducer();
    }
}
