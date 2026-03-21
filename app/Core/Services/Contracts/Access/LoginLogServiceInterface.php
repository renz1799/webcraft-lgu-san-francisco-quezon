<?php
// app/Services/Contracts/LoginLogServiceInterface.php

namespace App\Core\Services\Contracts\Access;

use App\Core\Models\User;
use Illuminate\Support\Collection;

interface LoginLogServiceInterface
{
    /**
     * Returns datatable payload: ['data'=>array, 'last_page'=>int, 'total'=>int].
     */
    public function datatable(array $params): array;

    /**
     * @return Collection<int, \App\Core\Models\LoginDetail>
     */
    public function recentForUser(User $user, int $limit = 4): Collection;
}
