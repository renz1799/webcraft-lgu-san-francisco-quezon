<?php

namespace App\Modules\GSO\Services\RIS;

use App\Modules\GSO\Models\Ris;
use App\Modules\GSO\Services\Contracts\RIS\RisPrintServiceInterface;

class RisPrintService implements RisPrintServiceInterface
{
    public function getPrintData(string $risId): array
    {
        $ris = Ris::query()
            ->with(['items', 'fundSource'])
            ->findOrFail($risId);

        $perPage = 28;

        $rows = $ris->items
            ->sortBy(fn ($it) => $it->line_no ?? 999999)
            ->values()
            ->map(fn ($it) => [
                'stock_no' => (string) ($it->stock_no_snapshot ?? ''),
                'unit' => (string) ($it->unit_snapshot ?? ''),
                'description' => (string) ($it->description_snapshot ?? ''),
                'qty_requested' => (int) ($it->qty_requested ?? 0),
                'qty_issued' => (int) ($it->qty_issued ?? 0),
                'remarks' => (string) ($it->remarks ?? ''),
            ])
            ->all();

        $pages = array_chunk($rows, $perPage);
        if (empty($pages)) {
            $pages = [[]];
        }

        $fundCode = trim((string) ($ris->fundSource?->code ?? ''));
        $fundName = trim((string) ($ris->fundSource?->name ?? ''));
        $fundDisplay = trim((string) ($ris->fund ?? ''));
        if ($fundDisplay === '') {
            $fundDisplay = trim(implode(' - ', array_filter([$fundCode, $fundName], fn ($value) => $value !== '')));
        }

        $print = [
            'ris_no' => $ris->ris_number ?? '',
            'ris_date' => $ris->ris_date?->format('m/d/Y') ?? '',
            'office' => $ris->requesting_department_name_snapshot ?? '',
            'fund' => $fundDisplay,
        ];

        return [
            'ris' => $ris,
            'print' => $print,
            'pages' => $pages,
            'totalPages' => count($pages),
        ];
    }
}
