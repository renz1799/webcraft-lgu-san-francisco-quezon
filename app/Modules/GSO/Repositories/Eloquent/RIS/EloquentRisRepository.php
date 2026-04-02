<?php

namespace App\Modules\GSO\Repositories\Eloquent\RIS;

use App\Modules\GSO\Models\Ris;
use App\Modules\GSO\Repositories\Contracts\RIS\RisRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EloquentRisRepository implements RisRepositoryInterface
{
    public function datatable(array $filters, int $page = 1, int $size = 15): array
    {
        $page = max(1, (int) $page);
        $size = max(1, (int) $size);

        $base = DB::table('ris')
            ->leftJoin('fund_sources', 'fund_sources.id', '=', 'ris.fund_source_id')
            ->select([
                'ris.id',
                'ris.ris_number',
                'ris.ris_date',
                'ris.fund',
                'ris.status',
                'ris.purpose',
                'ris.deleted_at',
                'fund_sources.name as fund_source_name',
            ]);

        $recordStatus = strtolower(trim((string) ($filters['record_status'] ?? '')));
        if ($recordStatus === 'archived') {
            $base->whereNotNull('ris.deleted_at');
        } elseif ($recordStatus !== 'all') {
            $base->whereNull('ris.deleted_at');
        }

        $recordsTotal = (clone $base)->count('ris.id');

        $filtered = clone $base;

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $filtered->where(function ($query) use ($search) {
                $query->where('ris.ris_number', 'like', "%{$search}%")
                    ->orWhere('ris.purpose', 'like', "%{$search}%")
                    ->orWhere('ris.fund', 'like', "%{$search}%")
                    ->orWhere('fund_sources.name', 'like', "%{$search}%");
            });
        }

        $workflowStatus = strtolower(trim((string) ($filters['status'] ?? '')));
        if ($workflowStatus !== '') {
            $filtered->whereRaw('LOWER(ris.status) = ?', [$workflowStatus]);
        }

        $dateFrom = trim((string) ($filters['date_from'] ?? ''));
        $dateTo = trim((string) ($filters['date_to'] ?? ''));
        if ($dateFrom !== '') {
            $filtered->whereDate('ris.ris_date', '>=', $dateFrom);
        }
        if ($dateTo !== '') {
            $filtered->whereDate('ris.ris_date', '<=', $dateTo);
        }

        $fund = trim((string) ($filters['fund'] ?? ''));
        if ($fund !== '') {
            $filtered->where(function ($query) use ($fund) {
                $query->where('fund_sources.name', 'like', "%{$fund}%")
                    ->orWhere('ris.fund', 'like', "%{$fund}%");
            });
        }

        $recordsFiltered = (clone $filtered)->count('ris.id');
        $lastPage = $recordsFiltered > 0 ? (int) ceil($recordsFiltered / $size) : 1;
        $page = min($page, $lastPage);

        $rows = (clone $filtered)
            ->orderByDesc('ris.ris_date')
            ->orderByDesc('ris.ris_number')
            ->forPage($page, $size)
            ->get()
            ->map(function ($row) {
                return [
                    'id' => (string) ($row->id ?? ''),
                    'ris_number' => (string) ($row->ris_number ?? ''),
                    'ris_date' => (string) ($row->ris_date ?? ''),
                    'fund' => $this->normalizeFundLabel(
                        (string) ($row->fund_source_name ?? ''),
                        (string) ($row->fund ?? ''),
                    ),
                    'status' => (string) ($row->status ?? ''),
                    'purpose' => (string) ($row->purpose ?? ''),
                    'deleted_at' => $row->deleted_at ? (string) $row->deleted_at : null,
                ];
            })
            ->values()
            ->all();

        return [
            'data' => $rows,
            'last_page' => $lastPage,
            'total' => (int) $recordsFiltered,
            'recordsTotal' => (int) $recordsTotal,
            'recordsFiltered' => (int) $recordsFiltered,
        ];
    }

    public function findById(string $id): ?Ris
    {
        return Ris::query()
            ->with(['items', 'files'])
            ->find($id);
    }

    public function findByAirId(string $airId): ?Ris
    {
        return Ris::query()
            ->where('air_id', $airId)
            ->first();
    }

    public function create(array $data): Ris
    {
        return Ris::query()->create($data);
    }

    public function update(Ris $ris, array $data): Ris
    {
        $ris->fill($data);
        $ris->save();

        return $ris->refresh();
    }

    public function delete(Ris $ris): void
    {
        $ris->delete();
    }

    public function restore(Ris $ris): void
    {
        $ris->restore();
    }

    private function normalizeFundLabel(string $name, string $fallback): string
    {
        $name = trim($name);
        if ($name !== '') {
            return $name;
        }

        $fallback = trim($fallback);
        if ($fallback === '') {
            return '';
        }

        $parts = explode(' - ', $fallback, 2);

        return trim(count($parts) === 2 ? $parts[1] : $fallback);
    }
}
