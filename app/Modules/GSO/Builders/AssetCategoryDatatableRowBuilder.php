<?php

namespace App\Modules\GSO\Builders;

use App\Modules\GSO\Builders\Contracts\AssetCategoryDatatableRowBuilderInterface;
use App\Modules\GSO\Models\AssetCategory;

class AssetCategoryDatatableRowBuilder implements AssetCategoryDatatableRowBuilderInterface
{
    public function build(AssetCategory $assetCategory): array
    {
        $isArchived = $assetCategory->deleted_at !== null;
        $type = $assetCategory->relationLoaded('type') ? $assetCategory->type : null;

        return [
            'id' => (string) $assetCategory->id,
            'asset_type_id' => (string) $assetCategory->asset_type_id,
            'asset_code' => (string) $assetCategory->asset_code,
            'asset_name' => (string) $assetCategory->asset_name,
            'account_group' => $this->nullableString($assetCategory->account_group),
            'created_at' => $assetCategory->created_at?->toDateTimeString(),
            'created_at_text' => $assetCategory->created_at?->format('M d, Y h:i A') ?? '-',
            'deleted_at' => $assetCategory->deleted_at?->toDateTimeString(),
            'deleted_at_text' => $assetCategory->deleted_at?->format('M d, Y h:i A'),
            'status' => $isArchived ? 'archived' : 'active',
            'is_archived' => $isArchived,
            'type' => $type ? [
                'id' => (string) $type->id,
                'type_code' => (string) $type->type_code,
                'type_name' => (string) $type->type_name,
            ] : null,
            'type_code' => $type?->type_code,
            'type_name' => $type?->type_name,
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }
}
