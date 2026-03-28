<?php

namespace App\Modules\GSO\Services\Contracts;

interface RrspReportServiceInterface
{
    public function getPrintViewData(
        ?string $fundSourceId,
        ?string $departmentId = null,
        ?string $accountableOfficerId = null,
        ?string $returnDate = null,
        array $signatories = [],
        ?string $requestedPaper = null,
        array $paperOverrides = []
    ): array;

    public function generatePdf(
        ?string $fundSourceId,
        ?string $departmentId = null,
        ?string $accountableOfficerId = null,
        ?string $returnDate = null,
        array $signatories = [],
        ?string $requestedPaper = null,
        array $paperOverrides = []
    ): string;
}
