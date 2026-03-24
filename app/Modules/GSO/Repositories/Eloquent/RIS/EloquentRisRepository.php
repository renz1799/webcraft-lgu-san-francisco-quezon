<?php

namespace App\Modules\GSO\Repositories\Eloquent\RIS;

use App\Models\Ris;
use App\Modules\GSO\Repositories\Contracts\RIS\RisRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EloquentRisRepository implements RisRepositoryInterface
{
    public function datatable(array $filters, int $page = 1, int $size = 15): array
    {
        $page = max(1, (int) $page);
        $size = max(1, (int) $size);

        $base = DB::table('ris')
            ->select([
                'id',
                'ris_number',
                'ris_date',
                'fund',
                'status',
                'purpose',
                'deleted_at',
            ]);

        $recordStatus = strtolower(trim((string) ($filters['record_status'] ?? '')));
        if ($recordStatus === 'archived') {
            $base->whereNotNull('deleted_at');
        } elseif ($recordStatus !== 'all') {
            $base->whereNull('deleted_at');
        }

        $recordsTotal = (clone $base)->count();

        $filtered = clone $base;

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $filtered->where(function ($query) use ($search) {
                $query->where('ris_number', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%")
                    ->orWhere('fund', 'like', "%{$search}%");
            });
        }

        $workflowStatus = strtolower(trim((string) ($filters['status'] ?? '')));
        if ($workflowStatus !== '') {
            $filtered->whereRaw('LOWER(status) = ?', [$workflowStatus]);
        }

        $dateFrom = trim((string) ($filters['date_from'] ?? ''));
        $dateTo = trim((string) ($filters['date_to'] ?? ''));
        if ($dateFrom !== '') {
            $filtered->whereDate('ris_date', '>=', $dateFrom);
        }
        if ($dateTo !== '') {
            $filtered->whereDate('ris_date', '<=', $dateTo);
        }

        $fund = trim((string) ($filters['fund'] ?? ''));
        if ($fund !== '') {
            $filtered->where('fund', 'like', "%{$fund}%");
        }

        $recordsFiltered = (clone $filtered)->count();
        $lastPage = $recordsFiltered > 0 ? (int) ceil($recordsFiltered / $size) : 1;
        $page = min($page, $lastPage);

        $rows = (clone $filtered)
            ->orderByDesc('ris_date')
            ->orderByDesc('ris_number')
            ->forPage($page, $size)
            ->get()
            ->map(fn ($row) => (array) $row)
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
}