<?php

namespace App\Modules\GSO\Services;

use App\Modules\GSO\Services\Contracts\StickerDirectPdfServiceInterface;
use App\Modules\GSO\Services\Contracts\StickerReportServiceInterface;

class StickerDirectPdfService implements StickerDirectPdfServiceInterface
{
    public function __construct(
        private readonly StickerReportServiceInterface $stickers,
    ) {}

    public function generate(array $filters = []): string
    {
        return $this->stickers->generatePdf($filters);
    }
}
