<?php

namespace App\Modules\GSO\Services\Contracts\ICS;

interface IcsPrintServiceInterface
{
    /**
     * @param  array<string, int>  $paperOverrides
     * @return array{report: array<string, mixed>, paperProfile: array<string, mixed>}
     */
    public function buildReport(string $icsId, ?string $requestedPaper = null, array $paperOverrides = []): array;

    /**
     * @param  array<string, int>  $paperOverrides
     */
    public function generatePdf(string $icsId, ?string $requestedPaper = null, array $paperOverrides = []): string;
}
