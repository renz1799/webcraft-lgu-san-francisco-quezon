<?php

namespace App\Repositories\Contracts;

use App\Models\LoginDetail;

interface LoginDetailRepositoryInterface
{
    public function create(array $data): LoginDetail;
}
