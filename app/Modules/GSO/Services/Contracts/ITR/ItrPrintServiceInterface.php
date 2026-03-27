<?php

namespace App\Modules\GSO\Services\Contracts\ITR;

interface ItrPrintServiceInterface
{
    /**
     * @param  array<string, int>  $paperOverrides
     * @return array<string, mixed>
     */
    public function buildReport(string $itrId, ?string $requestedPaper = null, array $paperOverrides = []): array;

    /**
     * @param  array<string, int>  $paperOverrides
     */
    public function generatePdf(string $itrId, ?string $requestedPaper = null, array $paperOverrides = []): string;
}


