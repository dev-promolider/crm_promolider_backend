<?php

namespace Promolider\Application\Marketing\UseCases\Reports;

use Promolider\Domain\Marketing\Ports\Out\ReportRepositoryInterface;

class GetGeneralReportsUseCase
{
    public function __construct(
        private readonly ReportRepositoryInterface $reportRepository
    ) {}

    public function execute(): array
    {
        return $this->reportRepository->getGeneralReports();
    }
}
