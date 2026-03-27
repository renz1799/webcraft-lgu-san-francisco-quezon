<?php

namespace App\Modules\GSO\Repositories\Eloquent\PTR;

use App\Modules\GSO\Models\Ptr;
use App\Modules\GSO\Repositories\Contracts\PTR\PtrRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EloquentPtrRepository implements PtrRepositoryInterface
{
    public function datatable(array $filters, int $page = 1, int $size = 15): array
    {
        $page = max(1, (int) $page);
        $size = max(1, min((int) $size, 100));

        $base = DB::table('ptrs')
            ->leftJoin('departments as from_departments', 'from_departments.id', '=', 'ptrs.from_department_id')
            ->leftJoin('departments as to_departments', 'to_departments.id', '=', 'ptrs.to_department_id')
            ->leftJoin('fund_sources as from_fund_sources', 'from_fund_sources.id', '=', 'ptrs.from_fund_source_id')
            ->leftJoin('fund_sources as to_fund_sources', 'to_fund_sources.id', '=', 'ptrs.to_fund_source_id')
            ->select([
                'ptrs.id',
                'ptrs.ptr_number',
                'ptrs.transfer_date',
                'ptrs.status',
                'ptrs.transfer_type',
                'ptrs.from_accountable_officer',
                'ptrs.to_accountable_officer',
                'ptrs.remarks',
                'ptrs.deleted_at',
                'from_departments.code as from_department_code',
                'from_departments.name as from_department_name',
                'to_departments.code as to_department_code',
                'to_departments.name as to_department_name',
                'from_fund_sources.code as from_fund_source_code',
                'from_fund_sources.name as from_fund_source_name',
                'to_fund_sources.code as to_fund_source_code',
                'to_fund_sources.name as to_fund_source_name',
                DB::raw('(select count(*) from ptr_items where ptr_items.ptr_id = ptrs.id and ptr_items.deleted_at is null) as items_count'),
            ]);

        $recordStatus = strtolower(trim((string) ($filters['record_status'] ?? '')));
        if ($recordStatus === 'archived') {
            $base->whereNotNull('ptrs.deleted_at');
        } elseif ($recordStatus !== 'all') {
            $base->whereNull('ptrs.deleted_at');
        }

        $recordsTotal = (clone $base)->count('ptrs.id');
        $filtered = clone $base;

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $filtered->where(function ($query) use ($search) {
                $query->where('ptrs.ptr_number', 'like', "%{$search}%")
                    ->orWhere('ptrs.transfer_type', 'like', "%{$search}%")
                    ->orWhere('ptrs.from_accountable_officer', 'like', "%{$search}%")
                    ->orWhere('ptrs.to_accountable_officer', 'like', "%{$search}%")
                    ->orWhere('ptrs.reason_for_transfer', 'like', "%{$search}%")
                    ->orWhere('ptrs.remarks', 'like', "%{$search}%")
                    ->orWhere('from_departments.name', 'like', "%{$search}%")
                    ->orWhere('from_departments.code', 'like', "%{$search}%")
                    ->orWhere('to_departments.name', 'like', "%{$search}%")
                    ->orWhere('to_departments.code', 'like', "%{$search}%")
                    ->orWhere('from_fund_sources.name', 'like', "%{$search}%")
                    ->orWhere('from_fund_sources.code', 'like', "%{$search}%")
                    ->orWhere('to_fund_sources.name', 'like', "%{$search}%")
                    ->orWhere('to_fund_sources.code', 'like', "%{$search}%");
            });
        }

        $workflowStatus = strtolower(trim((string) ($filters['status'] ?? '')));
        if ($workflowStatus !== '') {
            $filtered->whereRaw('LOWER(ptrs.status) = ?', [$workflowStatus]);
        }

        $dateFrom = trim((string) ($filters['date_from'] ?? ''));
        if ($dateFrom !== '') {
            $filtered->whereDate('ptrs.transfer_date', '>=', $dateFrom);
        }

        $dateTo = trim((string) ($filters['date_to'] ?? ''));
        if ($dateTo !== '') {
            $filtered->whereDate('ptrs.transfer_date', '<=', $dateTo);
        }

        $fromDepartmentId = trim((string) ($filters['from_department_id'] ?? ''));
        if ($fromDepartmentId !== '') {
            $filtered->where('ptrs.from_department_id', $fromDepartmentId);
        }

        $toDepartmentId = trim((string) ($filters['to_department_id'] ?? ''));
        if ($toDepartmentId !== '') {
            $filtered->where('ptrs.to_department_id', $toDepartmentId);
        }

        $fromFundSourceId = trim((string) ($filters['from_fund_source_id'] ?? ''));
        if ($fromFundSourceId !== '') {
            $filtered->where('ptrs.from_fund_source_id', $fromFundSourceId);
        }

        $toFundSourceId = trim((string) ($filters['to_fund_source_id'] ?? ''));
        if ($toFundSourceId !== '') {
            $filtered->where('ptrs.to_fund_source_id', $toFundSourceId);
        }

        $recordsFiltered = (clone $filtered)->count('ptrs.id');
        $lastPage = $recordsFiltered > 0 ? (int) ceil($recordsFiltered / $size) : 1;
        $page = min($page, $lastPage);

        $rows = (clone $filtered)
            ->orderByDesc('ptrs.transfer_date')
            ->orderByDesc('ptrs.created_at')
            ->forPage($page, $size)
            ->get()
            ->map(function ($row) {
                $fromDepartment = trim(
                    (string) (($row->from_department_code ?? '') !== '' ? ($row->from_department_code . ' - ') : '') .
                    (string) ($row->from_department_name ?? '')
                );
                $toDepartment = trim(
                    (string) (($row->to_department_code ?? '') !== '' ? ($row->to_department_code . ' - ') : '') .
                    (string) ($row->to_department_name ?? '')
                );
                $fromFundSource = trim(
                    (string) (($row->from_fund_source_code ?? '') !== '' ? ($row->from_fund_source_code . ' - ') : '') .
                    (string) ($row->from_fund_source_name ?? '')
                );
                $toFundSource = trim(
                    (string) (($row->to_fund_source_code ?? '') !== '' ? ($row->to_fund_source_code . ' - ') : '') .
                    (string) ($row->to_fund_source_name ?? '')
                );

                return [
                    'id' => (string) ($row->id ?? ''),
                    'ptr_number' => (string) ($row->ptr_number ?? ''),
                    'transfer_date' => $row->transfer_date ? (string) $row->transfer_date : '',
                    'status' => (string) ($row->status ?? ''),
                    'transfer_type' => (string) ($row->transfer_type ?? ''),
                    'from_accountable_officer' => (string) ($row->from_accountable_officer ?? ''),
                    'to_accountable_officer' => (string) ($row->to_accountable_officer ?? ''),
                    'from_department' => $fromDepartment !== '' ? $fromDepartment : '-',
                    'to_department' => $toDepartment !== '' ? $toDepartment : '-',
                    'from_fund_source' => $fromFundSource !== '' ? $fromFundSource : '-',
                    'to_fund_source' => $toFundSource !== '' ? $toFundSource : '-',
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

    public function create(array $data): Ptr
    {
        return Ptr::query()->create($data);
    }

    public function findOrFail(string $id): Ptr
    {
        return Ptr::query()->findOrFail($id);
    }

    public function update(Ptr $ptr, array $data): Ptr
    {
        $ptr->fill($data);
        $ptr->save();

        return $ptr->refresh();
    }

    public function findWithTrashedOrFail(string $id): Ptr
    {
        return Ptr::query()->withTrashed()->findOrFail($id);
    }

    public function softDelete(Ptr $ptr): void
    {
        $ptr->delete();
    }

    public function restore(Ptr $ptr): void
    {
        $ptr->restore();
    }
}
