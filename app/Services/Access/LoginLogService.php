<?php

namespace App\Services\Access;

use App\Repositories\Contracts\LoginDetailRepositoryInterface;
use App\Services\Contracts\LoginLogServiceInterface;
use Carbon\Carbon;

class LoginLogService implements LoginLogServiceInterface
{
    public function __construct(
        private readonly LoginDetailRepositoryInterface $loginDetails
    ) {}

    public function datatable(array $params): array
    {
        $page = max(1, (int)($params['page'] ?? 1));
        $size = max(1, min((int)($params['size'] ?? 15), 100));

        $search = trim((string)($params['search'] ?? $params['q'] ?? ''));
        $status = trim((string)($params['status'] ?? ''));
        $user = trim((string)($params['user'] ?? ''));
        $email = trim((string)($params['email'] ?? ''));
        $ipAddress = trim((string)($params['ip_address'] ?? ''));
        $device = trim((string)($params['device'] ?? ''));
        $dateFrom = trim((string)($params['date_from'] ?? ''));
        $dateTo = trim((string)($params['date_to'] ?? ''));

        $base = $this->loginDetails->datatableBaseQuery(joinUsers: true);
        $recordsTotal = $this->loginDetails->countAll();

        if ($search !== '') {
            $base->where(function ($sub) use ($search) {
                $sub->where('login_details.email', 'like', "%{$search}%")
                    ->orWhere('login_details.ip_address', 'like', "%{$search}%")
                    ->orWhere('login_details.device', 'like', "%{$search}%")
                    ->orWhere('login_details.address', 'like', "%{$search}%")
                    ->orWhere('login_details.reason', 'like', "%{$search}%")
                    ->orWhere('users.username', 'like', "%{$search}%");
            });
        }

        if ($status === 'success') {
            $base->where('login_details.success', true);
        } elseif ($status === 'failed') {
            $base->where('login_details.success', false);
        }

        if ($user !== '') {
            $base->where('users.username', 'like', "%{$user}%");
        }

        if ($email !== '') {
            $base->where('login_details.email', 'like', "%{$email}%");
        }

        if ($ipAddress !== '') {
            $base->where('login_details.ip_address', 'like', "%{$ipAddress}%");
        }

        if ($device !== '') {
            $base->where('login_details.device', 'like', "%{$device}%");
        }

        if ($dateFrom !== '') {
            try {
                $from = Carbon::createFromFormat('Y-m-d', $dateFrom)->startOfDay();
                $base->where('login_details.created_at', '>=', $from);
            } catch (\Throwable $_e) {
                // ignore invalid date input after request validation fallback
            }
        }

        if ($dateTo !== '') {
            try {
                $to = Carbon::createFromFormat('Y-m-d', $dateTo)->endOfDay();
                $base->where('login_details.created_at', '<=', $to);
            } catch (\Throwable $_e) {
                // ignore invalid date input after request validation fallback
            }
        }

        $recordsFiltered = (clone $base)->count();

        $sortField = $params['sorters'][0]['field'] ?? null;
        $sortDir = ($params['sorters'][0]['dir'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $sortMap = [
            'created_at' => 'login_details.created_at',
            'success' => 'login_details.success',
            'attempted' => 'login_details.email',
            'ip_address' => 'login_details.ip_address',
            'device' => 'login_details.device',
            'address' => 'login_details.address',
            'location' => 'login_details.location',
            'user' => 'users.username',
        ];

        if ($sortField && isset($sortMap[$sortField])) {
            $base->orderBy($sortMap[$sortField], $sortDir);
        } else {
            $base->orderBy('login_details.created_at', 'desc');
        }

        $paginator = $base->paginate($size, ['*'], 'page', $page);

        $rows = collect($paginator->items())
            ->map(function ($row) {
                $lat = $row->latitude ?? null;
                $lng = $row->longitude ?? null;

                $locationUrl = ($lat !== null && $lng !== null)
                    ? "https://www.google.com/maps?q={$lat},{$lng}"
                    : null;

                $createdAt = $row->created_at ?? null;
                $createdText = $createdAt ? Carbon::parse($createdAt)->format('M d, Y h:i A') : '-';

                return [
                    'id' => (string)($row->id ?? ''),
                    'success' => (bool)($row->success ?? false),
                    'reason' => $row->reason ?? null,
                    'user' => $row->username ?? '-',
                    'attempted' => $row->email ?? '-',
                    'ip_address' => $row->ip_address ?? '-',
                    'device' => $row->device ?? '-',
                    'address' => $row->address ?? '-',
                    'location' => $row->location ?? '-',
                    'location_url' => $locationUrl,
                    'created_at' => $createdAt,
                    'created_at_text' => $createdText,
                ];
            })
            ->values()
            ->all();

        return [
            'data' => $rows,
            'last_page' => $paginator->lastPage(),
            'total' => $recordsFiltered,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
        ];
    }
}

