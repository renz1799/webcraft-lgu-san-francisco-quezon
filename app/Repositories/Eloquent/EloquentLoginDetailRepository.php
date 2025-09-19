<?php

namespace App\Repositories\Eloquent;

use App\Models\LoginDetail;
use App\Repositories\Contracts\LoginDetailRepositoryInterface;

class EloquentLoginDetailRepository implements LoginDetailRepositoryInterface
{
    public function create(array $data): LoginDetail
    {
        return LoginDetail::create($data);
    }
}
