<?php

namespace App\Modules\GSO\Services\Contracts;

interface StickerReportServiceInterface
{
    public function getPrintViewData(array $filters = []): array;

    public function generatePdf(array $filters = []): string;

    public function generatePdfWithProgress(array $filters = [], ?callable $progress = null, ?string $outputPath = null): string;
}
