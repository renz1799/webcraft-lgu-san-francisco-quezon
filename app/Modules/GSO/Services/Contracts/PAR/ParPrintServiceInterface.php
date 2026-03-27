<?php

namespace App\Modules\GSO\Services\Contracts\PAR;

interface ParPrintServiceInterface
{
    /**
     * @return array<string, mixed>
     */
    public function buildReport(string $parId, ?string $requestedPaper = null, array $paperOverrides = []): array;

    public function generatePdf(string $parId, ?string $requestedPaper = null, array $paperOverrides = []): string;
}
