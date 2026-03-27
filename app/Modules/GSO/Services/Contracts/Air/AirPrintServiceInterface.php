<?php

namespace App\Modules\GSO\Services\Contracts\Air;

interface AirPrintServiceInterface
{
    /**
     * @return array<string, mixed>
     */
    public function buildReport(string $airId, ?string $requestedPaper = null, array $paperOverrides = []): array;

    public function generatePdf(string $airId, ?string $requestedPaper = null, array $paperOverrides = []): string;
}
