<?php

namespace App\Modules\GSO\Builders\Contracts;

use App\Modules\GSO\Models\Air;

interface AirDatatableRowBuilderInterface
{
    /**
     * @return array<string, mixed>
     */
    public function build(Air $air): array;
}
