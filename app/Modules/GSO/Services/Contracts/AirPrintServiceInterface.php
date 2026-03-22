<?php

namespace App\Modules\GSO\Services\Contracts;

interface AirPrintServiceInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getPrintViewData(string $airId): array;
}
