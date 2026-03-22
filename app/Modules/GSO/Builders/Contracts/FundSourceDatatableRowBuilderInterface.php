<?php

namespace App\Modules\GSO\Builders\Contracts;

use App\Modules\GSO\Models\FundSource;

interface FundSourceDatatableRowBuilderInterface
{
    public function build(FundSource $fundSource): array;
}
