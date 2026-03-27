<?php

namespace App\Modules\GSO\Services\Contracts\PTR;

interface PtrPrintServiceInterface
{
    /**
     * @return array{
     *   report: array<string, mixed>,
     *   paperProfile: array<string, mixed>
     * }
     */
    public function buildReport(string $ptrId, ?string $requestedPaper = null, array $paperOverrides = []): array;

    public function generatePdf(string $ptrId, ?string $requestedPaper = null, array $paperOverrides = []): string;
}
