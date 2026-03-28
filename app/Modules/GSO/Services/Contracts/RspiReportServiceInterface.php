<?php

namespace App\Modules\GSO\Services\Contracts;

interface RspiReportServiceInterface
{
    public function getPrintViewData(
        ?string $fundSourceId,
        ?string $departmentId = null,
        ?string $accountableOfficerId = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        array $signatories = [],
        ?string $requestedPaper = null,
        array $paperOverrides = []
    ): array;

    public function generatePdf(
        ?string $fundSourceId,
        ?string $departmentId = null,
        ?string $accountableOfficerId = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        array $signatories = [],
        ?string $requestedPaper = null,
        array $paperOverrides = []
    ): string;
}
