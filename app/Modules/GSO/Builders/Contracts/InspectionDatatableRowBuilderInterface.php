<?php

namespace App\Modules\GSO\Builders\Contracts;

use App\Modules\GSO\Models\Inspection;

interface InspectionDatatableRowBuilderInterface
{
    public function build(Inspection $inspection): array;
}
