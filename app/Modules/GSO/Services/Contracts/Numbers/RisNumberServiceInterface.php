<?php

namespace App\Modules\GSO\Services\Contracts\Numbers;

use App\Modules\GSO\Models\Ris;
use Carbon\CarbonInterface;

interface RisNumberServiceInterface
{
    public function generate(Ris $ris, ?CarbonInterface $date = null): string;
}
