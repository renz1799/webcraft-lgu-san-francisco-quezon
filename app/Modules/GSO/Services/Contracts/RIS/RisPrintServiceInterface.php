<?php

namespace App\Modules\GSO\Services\Contracts\RIS;

interface RisPrintServiceInterface
{
    public function getPrintData(string $risId): array;
}
