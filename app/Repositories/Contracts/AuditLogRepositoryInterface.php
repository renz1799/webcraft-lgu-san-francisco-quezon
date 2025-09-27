<?php

// app/Repositories/Contracts/AuditLogRepositoryInterface.php
namespace App\Repositories\Contracts;

use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AuditLogRepositoryInterface
{
    public function create(array $data): AuditLog;

    public function paginate(
        int $perPage = 50,
        array $filters = []
    ): LengthAwarePaginator;
}
