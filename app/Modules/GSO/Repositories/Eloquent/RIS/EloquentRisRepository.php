<?php

namespace App\Repositories\Eloquent;

use App\Models\Ris;
use App\Modules\GSO\Repositories\Contracts\RIS\RisRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EloquentRisRepository implements RisRepositoryInterface
{

public function datatable(array $filters, int $page = 1, int $size = 15): array
{
    $page = max(1, (int) $page);
    $size = max(1, (int) $size);

    // ---------------------------------------------------------
    // Base query — Query Builder (not Eloquent)
    // ---------------------------------------------------------
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

    // ---------------------------------------------------------
    // record_status filter (soft deletes on ris)
    // ---------------------------------------------------------
    $recordStatus = strtolower(trim((string) ($filters['record_status'] ?? '')));
    if ($recordStatus === 'archived') {
        $base->whereNotNull('deleted_at');
    } elseif ($recordStatus === 'all') {
        // no filter
    } else {
        $base->whereNull('deleted_at');
    }

    // ---------------------------------------------------------
    // recordsTotal (record_status-only)
    // ---------------------------------------------------------
    $recordsTotal = (clone $base)->count();

    // ---------------------------------------------------------
    // Apply filters (search + workflow status + advanced)
    // ---------------------------------------------------------
    $filtered = clone $base;

    // Search: ris_number, purpose, fund
    $search = trim((string) ($filters['search'] ?? ''));
    if ($search !== '') {
        $filtered->where(function ($qq) use ($search) {
            $qq->where('ris_number', 'like', "%{$search}%")
                ->orWhere('purpose', 'like', "%{$search}%")
                ->orWhere('fund', 'like', "%{$search}%");
        });
    }

    // Workflow status filter (draft/submitted/approved/issued/...)
    $workflowStatus = strtolower(trim((string) ($filters['status'] ?? '')));
    if ($workflowStatus !== '') {
        $filtered->whereRaw('LOWER(status) = ?', [$workflowStatus]);
    }

    // Date range (ris_date)
    $dateFrom = trim((string) ($filters['date_from'] ?? ''));
    $dateTo   = trim((string) ($filters['date_to'] ?? ''));
    if ($dateFrom !== '') $filtered->whereDate('ris_date', '>=', $dateFrom);
    if ($dateTo !== '')   $filtered->whereDate('ris_date', '<=', $dateTo);

    // Fund text (advanced)
    $fund = trim((string) ($filters['fund'] ?? ''));
    if ($fund !== '') {
        $filtered->where('fund', 'like', "%{$fund}%");
    }

    // ---------------------------------------------------------
    // recordsFiltered
    // ---------------------------------------------------------
    $recordsFiltered = (clone $filtered)->count();

    // ✅ last_page (stable for remote pagination)
    $lastPage = $recordsFiltered > 0 ? (int) ceil($recordsFiltered / $size) : 1;

    // ✅ clamp requested page to last page (prevents "broken" feeling)
    $page = min($page, $lastPage);

    // ---------------------------------------------------------
    // Pagination
    // ---------------------------------------------------------
    $rows = (clone $filtered)
        ->orderByDesc('ris_date')
        ->orderByDesc('ris_number')
        ->forPage($page, $size)
        ->get()
        ->map(function ($row) {
            return [
                'id' => (string) ($row->id ?? ''),
                'ris_number' => (string) ($row->ris_number ?? ''),
                'ris_date' => $row->ris_date ? (string) $row->ris_date : '',
                'fund' => (string) ($row->fund ?? ''),
                'status' => (string) ($row->status ?? ''),
                'purpose' => (string) ($row->purpose ?? ''),
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
        return Ris::query()->with(['items', 'files'])->find($id);
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
        $ris->delete(); // soft delete
    }

    public function restore(Ris $ris): void
    {
        $ris->restore();
    }
}
