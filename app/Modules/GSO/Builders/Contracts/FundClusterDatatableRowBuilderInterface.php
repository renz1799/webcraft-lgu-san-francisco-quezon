<?php

namespace App\Modules\GSO\Builders\Contracts;

use App\Modules\GSO\Models\FundCluster;

interface FundClusterDatatableRowBuilderInterface
{
    public function build(FundCluster $fundCluster): array;
}
