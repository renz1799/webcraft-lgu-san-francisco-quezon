<?php

namespace App\Modules\GSO\Services\Contracts;

interface StickerDirectPdfServiceInterface
{
    public function generate(array $filters = []): string;
}
