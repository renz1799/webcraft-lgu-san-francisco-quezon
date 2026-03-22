<?php

namespace App\Modules\GSO\Builders;

use App\Modules\GSO\Builders\Contracts\AssetTypeDatatableRowBuilderInterface;
use App\Modules\GSO\Models\AssetType;

class AssetTypeDatatableRowBuilder implements AssetTypeDatatableRowBuilderInterface
{
    public function build(AssetType $assetType): array
    {
        $isArchived = $assetType->deleted_at !== null;

        return [
            'id' => (string) $assetType->id,
            'type_code' => (string) $assetType->type_code,
            'type_name' => (string) $assetType->type_name,
            'created_at' => $assetType->created_at?->toDateTimeString(),
            'created_at_text' => $assetType->created_at?->format('M d, Y h:i A') ?? '-',
            'deleted_at' => $assetType->deleted_at?->toDateTimeString(),
            'deleted_at_text' => $assetType->deleted_at?->format('M d, Y h:i A'),
            'status' => $isArchived ? 'archived' : 'active',
            'is_archived' => $isArchived,
        ];
    }
}
