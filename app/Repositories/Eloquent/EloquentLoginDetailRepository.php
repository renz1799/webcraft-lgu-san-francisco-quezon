<?php

namespace App\Repositories\Eloquent;

use App\Models\LoginDetail;
use App\Repositories\Contracts\LoginDetailRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class EloquentLoginDetailRepository implements LoginDetailRepositoryInterface
{
    public function create(array $data): LoginDetail
    {
        return LoginDetail::create($data);
    }

    public function datatableBaseQuery(bool $joinUsers = false): Builder
    {
        $q = LoginDetail::query()->select('login_details.*');

        if ($joinUsers) {
            $q->leftJoin('users', 'users.id', '=', 'login_details.user_id')
              ->addSelect('users.username'); // useful for sorting/searching by username
        }

        return $q;
    }

    public function countAll(): int
    {
        return LoginDetail::count();
    }
}
