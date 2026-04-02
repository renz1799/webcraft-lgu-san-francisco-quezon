<?php

namespace App\Modules\GSO\Repositories\Eloquent\WMR;

use App\Modules\GSO\Models\Wmr;
use App\Modules\GSO\Repositories\Contracts\WMR\WmrRepositoryInterface;
use Illuminate\Support\Facades\DB;

class EloquentWmrRepository implements WmrRepositoryInterface
{
    public function datatable(array $filters, int $page = 1, int $size = 15): array
    {
        $page = max(1, (int) $page);
        $size = max(1, min((int) $size, 100));

        $base = DB::table('wmrs')
            ->leftJoin('fund_clusters', 'fund_clusters.id', '=', 'wmrs.fund_cluster_id')
            ->select([
                'wmrs.id',
                'wmrs.wmr_number',
                'wmrs.report_date',
                'wmrs.status',
                'wmrs.place_of_storage',
                'wmrs.remarks',
                'wmrs.deleted_at',
                'wmrs.custodian_name',
                'wmrs.approved_by_name',
                'fund_clusters.code as fund_cluster_code',
                'fund_clusters.name as fund_cluster_name',
                DB::raw('(select count(*) from wmr_items where wmr_items.wmr_id = wmrs.id and wmr_items.deleted_at is null) as items_count'),
            ]);

        $recordStatus = strtolower(trim((string) ($filters['record_status'] ?? '')));
        if ($recordStatus === 'archived') {
            $base->whereNotNull('wmrs.deleted_at');
        } elseif ($recordStatus !== 'all') {
            $base->whereNull('wmrs.deleted_at');
        }

        $recordsTotal = (clone $base)->count('wmrs.id');
        $filtered = clone $base;

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $filtered->where(function ($query) use ($search) {
                $query->where('wmrs.wmr_number', 'like', "%{$search}%")
                    ->orWhere('wmrs.place_of_storage', 'like', "%{$search}%")
                    ->orWhere('wmrs.remarks', 'like', "%{$search}%")
                    ->orWhere('wmrs.custodian_name', 'like', "%{$search}%")
                    ->orWhere('wmrs.approved_by_name', 'like', "%{$search}%")
                    ->orWhere('fund_clusters.code', 'like', "%{$search}%")
                    ->orWhere('fund_clusters.name', 'like', "%{$search}%");
            });
        }

        $workflowStatus = strtolower(trim((string) ($filters['status'] ?? '')));
        if ($workflowStatus !== '') {
            $filtered->whereRaw('LOWER(wmrs.status) = ?', [$workflowStatus]);
        }

        $dateFrom = trim((string) ($filters['date_from'] ?? ''));
        if ($dateFrom !== '') {
            $filtered->whereDate('wmrs.report_date', '>=', $dateFrom);
        }

        $dateTo = trim((string) ($filters['date_to'] ?? ''));
        if ($dateTo !== '') {
            $filtered->whereDate('wmrs.report_date', '<=', $dateTo);
        }

        $fundClusterId = trim((string) ($filters['fund_cluster_id'] ?? ''));
        if ($fundClusterId !== '') {
            $filtered->where('wmrs.fund_cluster_id', $fundClusterId);
        }

        $recordsFiltered = (clone $filtered)->count('wmrs.id');
        $lastPage = $recordsFiltered > 0 ? (int) ceil($recordsFiltered / $size) : 1;
        $page = min($page, $lastPage);

        $rows = (clone $filtered)
            ->orderByDesc('wmrs.report_date')
            ->orderByDesc('wmrs.created_at')
            ->forPage($page, $size)
            ->get()
            ->map(function ($row) {
                $fundCluster = trim((string) ($row->fund_cluster_name ?? ''));

                return [
                    'id' => (string) ($row->id ?? ''),
                    'wmr_number' => (string) ($row->wmr_number ?? ''),
                    'report_date' => $row->report_date ? (string) $row->report_date : '',
                    'status' => (string) ($row->status ?? ''),
                    'place_of_storage' => (string) ($row->place_of_storage ?? ''),
                    'custodian_name' => (string) ($row->custodian_name ?? ''),
                    'approved_by_name' => (string) ($row->approved_by_name ?? ''),
                    'fund_cluster' => $fundCluster !== '' ? $fundCluster : '-',
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

    public function create(array $data): Wmr
    {
        return Wmr::query()->create($data);
    }

    public function findOrFail(string $id): Wmr
    {
        return Wmr::query()->findOrFail($id);
    }

    public function update(Wmr $wmr, array $data): Wmr
    {
        $wmr->fill($data);
        $wmr->save();

        return $wmr->refresh();
    }

    public function findWithTrashedOrFail(string $id): Wmr
    {
        return Wmr::query()->withTrashed()->findOrFail($id);
    }

    public function softDelete(Wmr $wmr): void
    {
        $wmr->delete();
    }

    public function restore(Wmr $wmr): void
    {
        $wmr->restore();
    }
}

