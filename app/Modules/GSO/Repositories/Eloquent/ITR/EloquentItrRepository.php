<?php

namespace App\Modules\GSO\Repositories\Eloquent\ITR;

use App\Modules\GSO\Models\Itr;
use App\Modules\GSO\Repositories\Contracts\ITR\ItrRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EloquentItrRepository implements ItrRepositoryInterface
{
    public function datatable(array $filters, int $page = 1, int $size = 15): array
    {
        $page = max(1, (int) $page);
        $size = max(1, min((int) $size, 100));

        $base = DB::table('itrs')
            ->leftJoin('departments as from_departments', 'from_departments.id', '=', 'itrs.from_department_id')
            ->leftJoin('departments as to_departments', 'to_departments.id', '=', 'itrs.to_department_id')
            ->leftJoin('fund_sources as from_fund_sources', 'from_fund_sources.id', '=', 'itrs.from_fund_source_id')
            ->leftJoin('fund_sources as to_fund_sources', 'to_fund_sources.id', '=', 'itrs.to_fund_source_id')
            ->select([
                'itrs.id',
                'itrs.itr_number',
                'itrs.transfer_date',
                'itrs.status',
                'itrs.transfer_type',
                'itrs.from_accountable_officer',
                'itrs.to_accountable_officer',
                'itrs.remarks',
                'itrs.deleted_at',
                'from_departments.code as from_code',
                'from_departments.name as from_name',
                'to_departments.code as to_code',
                'to_departments.name as to_name',
                'from_fund_sources.code as from_fund_source_code',
                'from_fund_sources.name as from_fund_source_name',
                'to_fund_sources.code as to_fund_source_code',
                'to_fund_sources.name as to_fund_source_name',
                DB::raw('(select count(*) from itr_items where itr_items.itr_id = itrs.id and itr_items.deleted_at is null) as items_count'),
            ]);

        $recordStatus = strtolower(trim((string) ($filters['record_status'] ?? '')));
        if ($recordStatus === 'archived') {
            $base->whereNotNull('itrs.deleted_at');
        } elseif ($recordStatus !== 'all') {
            $base->whereNull('itrs.deleted_at');
        }

        $recordsTotal = (clone $base)->count('itrs.id');
        $filtered = clone $base;

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $filtered->where(function ($query) use ($search) {
                $query->where('itrs.itr_number', 'like', "%{$search}%")
                    ->orWhere('itrs.transfer_type', 'like', "%{$search}%")
                    ->orWhere('itrs.from_accountable_officer', 'like', "%{$search}%")
                    ->orWhere('itrs.to_accountable_officer', 'like', "%{$search}%")
                    ->orWhere('itrs.reason_for_transfer', 'like', "%{$search}%")
                    ->orWhere('itrs.remarks', 'like', "%{$search}%")
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
            $filtered->whereRaw('LOWER(itrs.status) = ?', [$workflowStatus]);
        }

        $dateFrom = trim((string) ($filters['date_from'] ?? ''));
        if ($dateFrom !== '') {
            $filtered->whereDate('itrs.transfer_date', '>=', $dateFrom);
        }

        $dateTo = trim((string) ($filters['date_to'] ?? ''));
        if ($dateTo !== '') {
            $filtered->whereDate('itrs.transfer_date', '<=', $dateTo);
        }

        $fromDepartmentId = trim((string) ($filters['from_department_id'] ?? ''));
        if ($fromDepartmentId !== '') {
            $filtered->where('itrs.from_department_id', $fromDepartmentId);
        }

        $toDepartmentId = trim((string) ($filters['to_department_id'] ?? ''));
        if ($toDepartmentId !== '') {
            $filtered->where('itrs.to_department_id', $toDepartmentId);
        }

        $fromFundSourceId = trim((string) ($filters['from_fund_source_id'] ?? ''));
        if ($fromFundSourceId !== '') {
            $filtered->where('itrs.from_fund_source_id', $fromFundSourceId);
        }

        $toFundSourceId = trim((string) ($filters['to_fund_source_id'] ?? ''));
        if ($toFundSourceId !== '') {
            $filtered->where('itrs.to_fund_source_id', $toFundSourceId);
        }

        $recordsFiltered = (clone $filtered)->count('itrs.id');
        $lastPage = $recordsFiltered > 0 ? (int) ceil($recordsFiltered / $size) : 1;
        $page = min($page, $lastPage);

        $rows = (clone $filtered)
            ->orderByDesc('itrs.transfer_date')
            ->orderByDesc('itrs.created_at')
            ->forPage($page, $size)
            ->get()
            ->map(function ($row) {
                $fromDepartment = trim(
                    (string) (($row->from_code ?? '') !== '' ? ($row->from_code . ' - ') : '') .
                    (string) ($row->from_name ?? '')
                );
                $toDepartment = trim(
                    (string) (($row->to_code ?? '') !== '' ? ($row->to_code . ' - ') : '') .
                    (string) ($row->to_name ?? '')
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
                    'itr_number' => (string) ($row->itr_number ?? ''),
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

    public function create(array $data): Itr
    {
        return Itr::query()->create($data);
    }

    public function findOrFail(string $id): Itr
    {
        return Itr::query()->findOrFail($id);
    }

    public function update(Itr $itr, array $data): Itr
    {
        $itr->fill($data);
        $itr->save();

        return $itr->refresh();
    }

    public function findWithTrashedOrFail(string $id): Itr
    {
        return Itr::query()->withTrashed()->findOrFail($id);
    }

    public function softDelete(Itr $itr): void
    {
        $itr->delete();
    }

    public function restore(Itr $itr): void
    {
        $itr->restore();
    }
}


