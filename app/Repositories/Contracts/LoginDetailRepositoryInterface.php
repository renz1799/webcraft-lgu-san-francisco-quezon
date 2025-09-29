<?php

namespace App\Repositories\Contracts;

use App\Models\LoginDetail;
use Illuminate\Database\Eloquent\Builder;

interface LoginDetailRepositoryInterface
{
    public function create(array $data): LoginDetail;

    /** Base query for DataTables (optionally join users for sorting/search by username). */
    public function datatableBaseQuery(bool $joinUsers = false): Builder;

    /** Total rows in login_details (unfiltered). */
    public function countAll(): int;
}
