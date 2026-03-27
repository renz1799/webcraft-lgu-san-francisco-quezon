<?php

namespace App\Modules\GSO\Services\Contracts\WMR;

interface WmrPrintServiceInterface
{
    /**
     * @param  array<string, int>  $paperOverrides
     * @return array<string, mixed>
     */
    public function buildReport(string $wmrId, ?string $requestedPaper = null, array $paperOverrides = []): array;

    /**
     * @param  array<string, int>  $paperOverrides
     */
    public function generatePdf(string $wmrId, ?string $requestedPaper = null, array $paperOverrides = []): string;
}
