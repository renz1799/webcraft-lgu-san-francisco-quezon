<?php

namespace App\Modules\GSO\Services\Contracts\RIS;

interface RisPrintServiceInterface
{
    /**
     * @return array<string, mixed>
     */
    public function buildReport(string $risId, ?string $requestedPaper = null): array;

    public function generatePdf(string $risId, ?string $requestedPaper = null): string;
}
