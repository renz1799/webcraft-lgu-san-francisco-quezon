<?php
// app/Services/LoginLogService.php

namespace App\Services;

use App\Repositories\Contracts\LoginDetailRepositoryInterface;
use App\Services\Contracts\LoginLogServiceInterface;

class LoginLogService implements LoginLogServiceInterface
{
    public function __construct(
        private readonly LoginDetailRepositoryInterface $repo
    ) {}

    public function datatable(array $p): array
    {
        $start    = (int) ($p['start']     ?? 0);
        $length   = (int) ($p['length']    ?? 20);
        $search   = (string)($p['search']  ?? '');
        $orderBy  = (string)($p['order_by'] ?? 'created_at');
        $orderDir = in_array(($p['order_dir'] ?? 'desc'), ['asc','desc'], true) ? $p['order_dir'] : 'desc';

        // allow ordering on these “column names” coming from DataTables
        $orderMap = [
            'status'     => 'login_details.success',   // if first column is named 'success'/'status'
            'success'    => 'login_details.success',
            'user'       => 'user',                   // special (users.username)
            'email'      => 'login_details.email',
            'ip_address' => 'login_details.ip_address',
            'device'     => 'login_details.device',
            'address'    => 'login_details.address',
            'location'   => 'login_details.location',
            'created_at' => 'login_details.created_at',
        ];
        [$orderCol, $userSort] = $this->resolveOrder($orderBy, $orderMap);

        // join users only if we need to sort/search by it
        $base = $this->repo->datatableBaseQuery(joinUsers: ($userSort || $search !== ''));

        $recordsTotal = $this->repo->countAll();

        // search
        if ($search !== '') {
            $like = '%'.str_replace('%','\%',$search).'%';
            $base->where(function ($q) use ($like) {
                $q->orWhere('login_details.email', 'like', $like)
                  ->orWhere('login_details.ip_address', 'like', $like)
                  ->orWhere('login_details.device', 'like', $like)
                  ->orWhere('login_details.address', 'like', $like)
                  ->orWhere('users.username', 'like', $like);
            });
        }

        // filtered count (distinct because of join)
        $recordsFiltered = (clone $base)->distinct('login_details.id')->count('login_details.id');

        // ordering
        if ($userSort) {
            $base->orderBy('users.username', $orderDir);
        } else {
            $base->orderBy($orderCol, $orderDir);
        }

        // page + minimal eager load
        $rows = $base->with(['user:id,username,email'])
                     ->skip($start)->take($length)->get();

        // map to the exact fields the DataTable expects (NO HTML here)
        $data = $rows->map(function ($r) {
            // pretty device if you have an accessor; fall back to raw
            $device = method_exists($r, 'getDeviceDetailsAttribute')
                ? ($r->device_details ?? $r->device)
                : $r->device;

            return [
                'status'       => $r->success ? 'success' : 'failed',
                'reason'       => $r->reason, // unknown_email, invalid_password, inactive, ok
                'user'         => $r->user->username ?? '—',
                'email'        => $r->email ?: '—',      // attempted email for failures; empty on success
                'ip_address'   => $r->ip_address ?: '—',
                'device'       => $device ?: '—',
                'address'      => $r->address ?: '—',
                'location_url' => $r->location ?: null,  // plain URL; JS builds the <a>
                'created_at'   => optional($r->created_at)->format('M d, Y h:i A'),
            ];
        })->all();

        return compact('recordsTotal','recordsFiltered','data');
    }

    private function resolveOrder(string $orderBy, array $map): array
    {
        $userSort = $orderBy === 'user';
        $key      = $map[$orderBy] ?? $map['created_at'];
        return [$key, $userSort];
    }
}
