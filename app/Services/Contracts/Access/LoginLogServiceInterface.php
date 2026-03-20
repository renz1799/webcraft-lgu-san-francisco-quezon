<?php
// app/Services/Contracts/LoginLogServiceInterface.php

namespace App\Services\Contracts\Access;

use App\Models\User;
use Illuminate\Support\Collection;

interface LoginLogServiceInterface
{
    /**
     * Returns datatable payload: ['data'=>array, 'last_page'=>int, 'total'=>int].
     */
    public function datatable(array $params): array;

    /**
     * @return Collection<int, \App\Models\LoginDetail>
     */
    public function recentForUser(User $user, int $limit = 4): Collection;
}
