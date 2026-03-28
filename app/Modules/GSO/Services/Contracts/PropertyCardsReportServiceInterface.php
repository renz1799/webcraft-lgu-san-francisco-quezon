<?php

namespace App\Modules\GSO\Services\Contracts;

interface PropertyCardsReportServiceInterface
{
    public function getPrintViewData(array $filters = [], ?string $requestedPaper = null): array;

    public function generatePdf(array $filters = [], ?string $requestedPaper = null): string;
}
