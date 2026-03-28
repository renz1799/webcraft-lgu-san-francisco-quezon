<?php

namespace App\Modules\GSO\Services\Contracts;

interface RpcspReportServiceInterface
{
    public function getPrintViewData(
        ?string $fundSourceId,
        ?string $departmentId = null,
        ?string $accountableOfficerId = null,
        ?string $asOf = null,
        bool $prefillCount = false,
        array $signatories = [],
        ?string $requestedPaper = null,
        array $paperOverrides = []
    ): array;

    public function generatePdf(
        ?string $fundSourceId,
        ?string $departmentId = null,
        ?string $accountableOfficerId = null,
        ?string $asOf = null,
        bool $prefillCount = false,
        array $signatories = [],
        ?string $requestedPaper = null,
        array $paperOverrides = []
    ): string;
}
