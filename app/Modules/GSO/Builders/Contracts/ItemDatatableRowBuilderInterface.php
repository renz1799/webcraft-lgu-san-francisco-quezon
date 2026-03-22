<?php

namespace App\Modules\GSO\Builders\Contracts;

use App\Modules\GSO\Models\Item;

interface ItemDatatableRowBuilderInterface
{
    /**
     * @return array<string, mixed>
     */
    public function build(Item $item): array;
}
