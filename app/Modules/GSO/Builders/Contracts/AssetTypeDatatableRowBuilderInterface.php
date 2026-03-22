<?php

namespace App\Modules\GSO\Builders\Contracts;

use App\Modules\GSO\Models\AssetType;

interface AssetTypeDatatableRowBuilderInterface
{
    public function build(AssetType $assetType): array;
}
