<?php

namespace App\Modules\GSO\Builders;

use App\Modules\GSO\Builders\Contracts\FundSourceDatatableRowBuilderInterface;
use App\Modules\GSO\Models\FundSource;

class FundSourceDatatableRowBuilder implements FundSourceDatatableRowBuilderInterface
{
    public function build(FundSource $fundSource): array
    {
        $isArchived = $fundSource->deleted_at !== null;
        $fundCluster = $fundSource->relationLoaded('fundCluster') ? $fundSource->fundCluster : null;

        return [
            'id' => (string) $fundSource->id,
            'code' => $this->nullableString($fundSource->code),
            'name' => (string) $fundSource->name,
            'fund_cluster_id' => $fundSource->fund_cluster_id ? (string) $fundSource->fund_cluster_id : null,
            'fund_cluster_label' => $fundCluster
                ? $this->clusterLabel((string) $fundCluster->code, (string) $fundCluster->name)
                : 'None',
            'fund_cluster' => $fundCluster ? [
                'id' => (string) $fundCluster->id,
                'code' => (string) $fundCluster->code,
                'name' => (string) $fundCluster->name,
            ] : null,
            'is_active' => (bool) $fundSource->is_active,
            'is_active_text' => $fundSource->is_active ? 'Active' : 'Inactive',
            'created_at' => $fundSource->created_at?->toDateTimeString(),
            'created_at_text' => $fundSource->created_at?->format('M d, Y h:i A') ?? '-',
            'deleted_at' => $fundSource->deleted_at?->toDateTimeString(),
            'deleted_at_text' => $fundSource->deleted_at?->format('M d, Y h:i A'),
            'status' => $isArchived ? 'archived' : 'active',
            'is_archived' => $isArchived,
            'label' => $this->fundSourceLabel($fundSource),
        ];
    }

    private function fundSourceLabel(FundSource $fundSource): string
    {
        $code = trim((string) ($fundSource->code ?? ''));
        $name = trim((string) $fundSource->name);

        if ($code !== '' && $name !== '') {
            return "{$code} - {$name}";
        }

        return $code !== '' ? $code : ($name !== '' ? $name : 'Fund Source');
    }

    private function clusterLabel(string $code, string $name): string
    {
        $code = trim($code);
        $name = trim($name);

        if ($code !== '' && $name !== '') {
            return "{$code} - {$name}";
        }

        return $code !== '' ? $code : ($name !== '' ? $name : 'Fund Cluster');
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value !== '' ? $value : null;
    }
}
