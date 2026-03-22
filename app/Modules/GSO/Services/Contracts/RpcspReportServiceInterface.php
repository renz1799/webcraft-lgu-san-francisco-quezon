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
        array $signatories = []
    ): array;
}
