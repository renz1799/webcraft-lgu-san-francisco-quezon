<?php

namespace App\Core\Repositories\Eloquent;

use App\Core\Models\LoginDetail;
use App\Core\Repositories\Contracts\LoginDetailRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class EloquentLoginDetailRepository implements LoginDetailRepositoryInterface
{
    public function create(array $data): LoginDetail
    {
        return LoginDetail::create($data);
    }

    public function recentForUser(string $moduleId, string $userId, int $limit = 4): Collection
    {
        return LoginDetail::query()
            ->where('module_id', $moduleId)
            ->where('user_id', $userId)
            ->latest()
            ->take(max(1, $limit))
            ->get();
    }

    public function datatable(string $moduleId, array $filters, int $page = 1, int $size = 15): array
    {
        $page = max(1, (int) $page);
        $size = max(1, min((int) $size, 100));

        $recordsTotal = (clone $this->buildBaseDatatableQuery($moduleId))->count();

        $filteredForCount = $this->buildFilteredDatatableQuery($moduleId, $filters);
        $recordsFiltered = (clone $filteredForCount)->count();

        $lastPage = $recordsFiltered > 0 ? (int) ceil($recordsFiltered / $size) : 1;
        $page = min($page, $lastPage);

        $rows = $this->applyDatatableSort(
            $this->buildFilteredDatatableQuery($moduleId, $filters),
            $filters
        )
            ->forPage($page, $size)
            ->get()
            ->map(fn (LoginDetail $log) => $this->mapDatatableRow($log))
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

    private function buildBaseDatatableQuery(string $moduleId): Builder
    {
        return LoginDetail::query()
            ->where('login_details.module_id', $moduleId)
            ->leftJoin('users', 'users.id', '=', 'login_details.user_id')
            ->select('login_details.*')
            ->addSelect('users.username');
    }

    private function buildFilteredDatatableQuery(string $moduleId, array $filters): Builder
    {
        $query = $this->buildBaseDatatableQuery($moduleId);

        $search = trim((string) ($filters['search'] ?? $filters['q'] ?? ''));
        if ($search !== '') {
            $query->where(function (Builder $sub) use ($search) {
                $sub->where('login_details.email', 'like', "%{$search}%")
                    ->orWhere('login_details.ip_address', 'like', "%{$search}%")
                    ->orWhere('login_details.device', 'like', "%{$search}%")
                    ->orWhere('login_details.address', 'like', "%{$search}%")
                    ->orWhere('login_details.reason', 'like', "%{$search}%")
                    ->orWhere('users.username', 'like', "%{$search}%");
            });
        }

        $status = trim((string) ($filters['status'] ?? ''));
        if ($status === 'success') {
            $query->where('login_details.success', true);
        } elseif ($status === 'failed') {
            $query->where('login_details.success', false);
        }

        $user = trim((string) ($filters['user'] ?? ''));
        if ($user !== '') {
            $query->where('users.username', 'like', "%{$user}%");
        }

        $email = trim((string) ($filters['email'] ?? ''));
        if ($email !== '') {
            $query->where('login_details.email', 'like', "%{$email}%");
        }

        $ipAddress = trim((string) ($filters['ip_address'] ?? ''));
        if ($ipAddress !== '') {
            $query->where('login_details.ip_address', 'like', "%{$ipAddress}%");
        }

        $device = trim((string) ($filters['device'] ?? ''));
        if ($device !== '') {
            $query->where('login_details.device', 'like', "%{$device}%");
        }

        $dateFrom = trim((string) ($filters['date_from'] ?? ''));
        if ($dateFrom !== '') {
            try {
                $from = Carbon::createFromFormat('Y-m-d', $dateFrom)->startOfDay();
                $query->where('login_details.created_at', '>=', $from);
            } catch (\Throwable $_e) {
                // ignored after request validation
            }
        }

        $dateTo = trim((string) ($filters['date_to'] ?? ''));
        if ($dateTo !== '') {
            try {
                $to = Carbon::createFromFormat('Y-m-d', $dateTo)->endOfDay();
                $query->where('login_details.created_at', '<=', $to);
            } catch (\Throwable $_e) {
                // ignored after request validation
            }
        }

        return $query;
    }

    private function applyDatatableSort(Builder $query, array $filters): Builder
    {
        $sortField = $filters['sorters'][0]['field'] ?? null;
        $sortDir = (($filters['sorters'][0]['dir'] ?? 'desc') === 'asc') ? 'asc' : 'desc';

        $map = [
            'created_at' => 'login_details.created_at',
            'success' => 'login_details.success',
            'attempted' => 'login_details.email',
            'ip_address' => 'login_details.ip_address',
            'device' => 'login_details.device',
            'address' => 'login_details.address',
            'location' => 'login_details.location',
            'user' => 'users.username',
        ];

        if ($sortField && isset($map[$sortField])) {
            return $query->orderBy($map[$sortField], $sortDir);
        }

        return $query->orderBy('login_details.created_at', 'desc');
    }

    private function mapDatatableRow(LoginDetail $log): array
    {
        $lat = $log->latitude;
        $lng = $log->longitude;

        $locationUrl = ($lat !== null && $lng !== null)
            ? "https://www.google.com/maps?q={$lat},{$lng}"
            : null;

        $createdAt = $log->created_at;

        return [
            'id' => (string) ($log->id ?? ''),
            'success' => (bool) ($log->success ?? false),
            'reason' => $log->reason ?? null,
            'user' => $log->username ?? '-',
            'attempted' => $log->email ?? '-',
            'ip_address' => $log->ip_address ?? '-',
            'device' => $log->device ?? '-',
            'address' => $log->address ?? '-',
            'location' => $log->location ?? '-',
            'location_url' => $locationUrl,
            'created_at' => $createdAt,
            'created_at_text' => $createdAt ? Carbon::parse($createdAt)->format('M d, Y h:i A') : '-',
        ];
    }
}
