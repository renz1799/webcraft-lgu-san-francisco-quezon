<?php

namespace App\Modules\GSO\Repositories\Eloquent\PAR;

use App\Modules\GSO\Models\Par;
use App\Modules\GSO\Repositories\Contracts\PAR\ParRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EloquentParRepository implements ParRepositoryInterface
{
    public function datatable(array $filters, int $page = 1, int $size = 15): array
    {
        $page = max(1, (int) $page);
        $size = max(1, (int) $size);

        $base = DB::table('pars')
            ->select([
                'pars.id',
                'pars.par_number',
                'pars.issued_date',
                'pars.status',
                'pars.person_accountable',
                'pars.department_id',
                'pars.remarks',
                'pars.deleted_at',
                DB::raw('(select count(*) from par_items where par_items.par_id = pars.id and par_items.deleted_at is null) as items_count'),
            ])
            ->leftJoin('departments', 'departments.id', '=', 'pars.department_id')
            ->addSelect([
                'departments.code as department_code',
                'departments.name as department_name',
            ]);

        $recordStatus = strtolower(trim((string) ($filters['record_status'] ?? '')));
        if ($recordStatus === 'archived') {
            $base->whereNotNull('pars.deleted_at');
        } elseif ($recordStatus !== 'all') {
            $base->whereNull('pars.deleted_at');
        }

        $recordsTotal = (clone $base)->count('pars.id');
        $filtered = clone $base;

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $filtered->where(function ($query) use ($search) {
                $query->where('pars.par_number', 'like', "%{$search}%")
                    ->orWhere('pars.person_accountable', 'like', "%{$search}%")
                    ->orWhere('pars.remarks', 'like', "%{$search}%")
                    ->orWhere('departments.name', 'like', "%{$search}%")
                    ->orWhere('departments.code', 'like', "%{$search}%");
            });
        }

        $workflowStatus = strtolower(trim((string) ($filters['status'] ?? '')));
        if ($workflowStatus !== '') {
            $filtered->whereRaw('LOWER(pars.status) = ?', [$workflowStatus]);
        }

        $dateFrom = trim((string) ($filters['date_from'] ?? ''));
        $dateTo = trim((string) ($filters['date_to'] ?? ''));
        if ($dateFrom !== '') {
            $filtered->whereDate('pars.issued_date', '>=', $dateFrom);
        }
        if ($dateTo !== '') {
            $filtered->whereDate('pars.issued_date', '<=', $dateTo);
        }

        $departmentId = trim((string) ($filters['department_id'] ?? ''));
        if ($departmentId !== '') {
            $filtered->where('pars.department_id', $departmentId);
        }

        $recordsFiltered = (clone $filtered)->count('pars.id');
        $lastPage = $recordsFiltered > 0 ? (int) ceil($recordsFiltered / $size) : 1;
        $page = min($page, $lastPage);

        $rows = (clone $filtered)
            ->orderByDesc('pars.issued_date')
            ->orderByDesc('pars.par_number')
            ->forPage($page, $size)
            ->get()
            ->map(function (object $row): array {
                $departmentCode = trim((string) ($row->department_code ?? ''));
                $departmentName = trim((string) ($row->department_name ?? ''));
                $department = trim(($departmentCode !== '' ? $departmentCode . ' - ' : '') . $departmentName);

                return [
                    'id' => (string) ($row->id ?? ''),
                    'par_number' => (string) ($row->par_number ?? ''),
                    'issued_date' => $row->issued_date ? (string) $row->issued_date : '',
                    'status' => (string) ($row->status ?? ''),
                    'person_accountable' => (string) ($row->person_accountable ?? ''),
                    'department' => $department !== '' ? $department : '-',
                    'items_count' => (int) ($row->items_count ?? 0),
                    'remarks' => (string) ($row->remarks ?? ''),
                ];
            })
            ->values()
            ->all();

        Log::info('ParRepository@datatable', [
            'page' => $page,
            'size' => $size,
            'recordStatus' => $recordStatus,
            'workflowStatus' => $workflowStatus,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'lastPage' => $lastPage,
            'returned' => count($rows),
        ]);

        return [
            'data' => $rows,
            'last_page' => $lastPage,
            'total' => (int) $recordsFiltered,
            'recordsTotal' => (int) $recordsTotal,
            'recordsFiltered' => (int) $recordsFiltered,
        ];
    }

    public function create(array $data): Par
    {
        return Par::query()->create($data);
    }

    public function findOrFail(string $id): Par
    {
        return Par::query()->findOrFail($id);
    }

    public function update(Par $par, array $data): Par
    {
        $par->fill($data);
        $par->save();

        return $par->refresh();
    }

    public function findWithTrashedOrFail(string $id): Par
    {
        return Par::query()->withTrashed()->findOrFail($id);
    }

    public function softDelete(Par $par): void
    {
        $par->delete();
    }

    public function restore(Par $par): void
    {
        $par->restore();
    }
}
