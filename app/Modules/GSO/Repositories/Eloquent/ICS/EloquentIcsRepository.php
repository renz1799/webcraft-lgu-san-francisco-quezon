<?php

namespace App\Modules\GSO\Repositories\Eloquent\ICS;

use App\Modules\GSO\Models\Ics;
use App\Modules\GSO\Repositories\Contracts\ICS\IcsRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EloquentIcsRepository implements IcsRepositoryInterface
{
    public function datatable(array $filters, int $page = 1, int $size = 15): array
    {
        $page = max(1, (int) $page);
        $size = max(1, min((int) $size, 100));

        $base = DB::table('ics')
            ->leftJoin('departments', 'departments.id', '=', 'ics.department_id')
            ->leftJoin('fund_sources', 'fund_sources.id', '=', 'ics.fund_source_id')
            ->select([
                'ics.id',
                'ics.ics_number',
                'ics.issued_date',
                'ics.status',
                'ics.received_from_name',
                'ics.received_by_name',
                'ics.received_by_office',
                'ics.remarks',
                'ics.deleted_at',
                'departments.code as department_code',
                'departments.name as department_name',
                'fund_sources.code as fund_source_code',
                'fund_sources.name as fund_source_name',
                DB::raw('(select count(*) from ics_items where ics_items.ics_id = ics.id and ics_items.deleted_at is null) as items_count'),
            ]);

        $recordStatus = strtolower(trim((string) ($filters['record_status'] ?? '')));
        if ($recordStatus === 'archived') {
            $base->whereNotNull('ics.deleted_at');
        } elseif ($recordStatus !== 'all') {
            $base->whereNull('ics.deleted_at');
        }

        $recordsTotal = (clone $base)->count('ics.id');
        $filtered = clone $base;

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $filtered->where(function ($query) use ($search) {
                $query->where('ics.ics_number', 'like', "%{$search}%")
                    ->orWhere('ics.received_from_name', 'like', "%{$search}%")
                    ->orWhere('ics.received_by_name', 'like', "%{$search}%")
                    ->orWhere('ics.received_by_office', 'like', "%{$search}%")
                    ->orWhere('ics.remarks', 'like', "%{$search}%")
                    ->orWhere('departments.name', 'like', "%{$search}%")
                    ->orWhere('departments.code', 'like', "%{$search}%")
                    ->orWhere('fund_sources.name', 'like', "%{$search}%")
                    ->orWhere('fund_sources.code', 'like', "%{$search}%");
            });
        }

        $workflowStatus = strtolower(trim((string) ($filters['status'] ?? '')));
        if ($workflowStatus !== '') {
            $filtered->whereRaw('LOWER(ics.status) = ?', [$workflowStatus]);
        }

        $dateFrom = trim((string) ($filters['date_from'] ?? ''));
        if ($dateFrom !== '') {
            $filtered->whereDate('ics.issued_date', '>=', $dateFrom);
        }

        $dateTo = trim((string) ($filters['date_to'] ?? ''));
        if ($dateTo !== '') {
            $filtered->whereDate('ics.issued_date', '<=', $dateTo);
        }

        $departmentId = trim((string) ($filters['department_id'] ?? ''));
        if ($departmentId !== '') {
            $filtered->where('ics.department_id', $departmentId);
        }

        $fundSourceId = trim((string) ($filters['fund_source_id'] ?? ''));
        if ($fundSourceId !== '') {
            $filtered->where('ics.fund_source_id', $fundSourceId);
        }

        $recordsFiltered = (clone $filtered)->count('ics.id');
        $lastPage = $recordsFiltered > 0 ? (int) ceil($recordsFiltered / $size) : 1;
        $page = min($page, $lastPage);

        $rows = (clone $filtered)
            ->orderByDesc('ics.issued_date')
            ->orderByDesc('ics.created_at')
            ->forPage($page, $size)
            ->get()
            ->map(function (object $row): array {
                $departmentCode = trim((string) ($row->department_code ?? ''));
                $departmentName = trim((string) ($row->department_name ?? ''));
                $fundSourceCode = trim((string) ($row->fund_source_code ?? ''));
                $fundSourceName = trim((string) ($row->fund_source_name ?? ''));

                return [
                    'id' => (string) ($row->id ?? ''),
                    'ics_number' => (string) ($row->ics_number ?? ''),
                    'issued_date' => $row->issued_date ? (string) $row->issued_date : '',
                    'status' => (string) ($row->status ?? ''),
                    'received_by_name' => (string) ($row->received_by_name ?? ''),
                    'received_by_office' => (string) ($row->received_by_office ?? ''),
                    'department' => trim(($departmentCode !== '' ? $departmentCode . ' - ' : '') . $departmentName) ?: '-',
                    'fund_source' => trim(($fundSourceCode !== '' ? $fundSourceCode . ' - ' : '') . $fundSourceName) ?: '-',
                    'items_count' => (int) ($row->items_count ?? 0),
                    'remarks' => (string) ($row->remarks ?? ''),
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

    public function create(array $data): Ics
    {
        return Ics::query()->create($data);
    }

    public function findOrFail(string $id): Ics
    {
        return Ics::query()->findOrFail($id);
    }

    public function update(Ics $ics, array $data): Ics
    {
        $ics->fill($data);
        $ics->save();

        return $ics->refresh();
    }

    public function findWithTrashedOrFail(string $id): Ics
    {
        return Ics::query()->withTrashed()->findOrFail($id);
    }

    public function softDelete(Ics $ics): void
    {
        $ics->delete();
    }

    public function restore(Ics $ics): void
    {
        $ics->restore();
    }
}
