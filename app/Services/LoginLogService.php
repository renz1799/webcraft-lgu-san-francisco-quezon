<?php

namespace App\Services;

use App\Repositories\Contracts\LoginDetailRepositoryInterface;
use App\Services\Contracts\LoginLogServiceInterface;
use Illuminate\Support\Facades\Log;

class LoginLogService implements LoginLogServiceInterface
{
    public function __construct(
        private readonly LoginDetailRepositoryInterface $loginDetails
    ) {}

    public function datatable(array $params): array
    {
        Log::info('LoginLogService@datatable incoming', ['params' => $params]);

        $page = (int)($params['page'] ?? 1);
        $size = (int)($params['size'] ?? 20);
        $q    = trim((string)($params['q'] ?? ''));

        // joinUsers = true so we can search/sort username if needed
        $base = $this->loginDetails->datatableBaseQuery(joinUsers: true);

        $recordsTotal = $this->loginDetails->countAll();

        if ($q !== '') {
            $base->where(function ($sub) use ($q) {
                $sub->where('login_details.email', 'like', "%{$q}%")
                    ->orWhere('login_details.ip_address', 'like', "%{$q}%")
                    ->orWhere('login_details.device', 'like', "%{$q}%")
                    ->orWhere('login_details.address', 'like', "%{$q}%")
                    ->orWhere('users.username', 'like', "%{$q}%");
            });
        }

        $recordsFiltered = (clone $base)->count();

        // sorting (Tabulator sends sorters[0][field], sorters[0][dir])
        $sortField = $params['sorters'][0]['field'] ?? null;
        $sortDir   = $params['sorters'][0]['dir'] ?? 'desc';

        // Map tabulator field -> DB column (important when joined)
        $sortMap = [
            'created_at' => 'login_details.created_at',
            'success'    => 'login_details.success',
            'attempted'  => 'login_details.email',
            'ip_address' => 'login_details.ip_address',
            'device'     => 'login_details.device',
            'address'    => 'login_details.address',
            'location'   => 'login_details.location',
            'user'       => 'users.username',
        ];

        if ($sortField && isset($sortMap[$sortField])) {
            $base->orderBy($sortMap[$sortField], $sortDir === 'asc' ? 'asc' : 'desc');
        } else {
            $base->orderBy('login_details.created_at', 'desc');
        }

        // paginate manually (Builder paginate works)
        $paginator = $base->paginate($size, ['*'], 'page', $page);

        $rows = collect($paginator->items())->map(function ($row) {
            // $row is stdClass because of join/select - safe access via ->field
            $lat = $row->latitude ?? null;
            $lng = $row->longitude ?? null;

            $locationUrl = ($lat !== null && $lng !== null)
                ? "https://www.google.com/maps?q={$lat},{$lng}"
                : null;

            $createdAt = $row->created_at ?? null;
            $createdHuman = $createdAt ? \Carbon\Carbon::parse($createdAt)->format('M d, Y h:i A') : '—';

            return [
                'id'               => (string)($row->id ?? ''),
                'success'          => (bool)($row->success ?? false),
                'reason'           => $row->reason ?? null,
                'user'             => $row->username ?? '—',
                'attempted'        => $row->email ?? '—',
                'ip_address'       => $row->ip_address ?? '—',
                'device'           => $row->device ?? '—',
                'address'          => $row->address ?? '—',
                'location'         => $row->location ?? '—',
                'location_url'     => $locationUrl,
                'created_at'       => $createdAt,
                'created_at_human' => $createdHuman,
            ];
        })->values()->all();

        $result = [
            'data'            => $rows,
            'last_page'       => $paginator->lastPage(),
            'total'           => $recordsFiltered,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
        ];

        Log::info('LoginLogService@datatable result', [
            'rows' => count($rows),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'page' => $page,
            'size' => $size,
        ]);

        return $result;
    }
}
