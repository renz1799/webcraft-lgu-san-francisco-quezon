<?php

namespace App\Modules\GSO\Builders;

use App\Modules\GSO\Builders\Contracts\FundClusterDatatableRowBuilderInterface;
use App\Modules\GSO\Models\FundCluster;

class FundClusterDatatableRowBuilder implements FundClusterDatatableRowBuilderInterface
{
    public function build(FundCluster $fundCluster): array
    {
        $isArchived = $fundCluster->deleted_at !== null;

        return [
            'id' => (string) $fundCluster->id,
            'code' => (string) $fundCluster->code,
            'name' => (string) $fundCluster->name,
            'is_active' => (bool) $fundCluster->is_active,
            'is_active_text' => $fundCluster->is_active ? 'Active' : 'Inactive',
            'created_at' => $fundCluster->created_at?->toDateTimeString(),
            'created_at_text' => $fundCluster->created_at?->format('M d, Y h:i A') ?? '-',
            'deleted_at' => $fundCluster->deleted_at?->toDateTimeString(),
            'deleted_at_text' => $fundCluster->deleted_at?->format('M d, Y h:i A'),
            'status' => $isArchived ? 'archived' : 'active',
            'is_archived' => $isArchived,
            'label' => $this->label($fundCluster),
        ];
    }

    private function label(FundCluster $fundCluster): string
    {
        $code = trim((string) $fundCluster->code);
        $name = trim((string) $fundCluster->name);

        if ($code !== '' && $name !== '') {
            return "{$code} - {$name}";
        }

        return $code !== '' ? $code : ($name !== '' ? $name : 'Fund Cluster');
    }
}
