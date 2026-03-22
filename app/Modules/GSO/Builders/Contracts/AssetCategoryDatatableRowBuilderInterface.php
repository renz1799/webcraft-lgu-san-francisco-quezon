<?php

namespace App\Modules\GSO\Builders\Contracts;

use App\Modules\GSO\Models\AssetCategory;

interface AssetCategoryDatatableRowBuilderInterface
{
    public function build(AssetCategory $assetCategory): array;
}
