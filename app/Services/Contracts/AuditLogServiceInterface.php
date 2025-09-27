<?php
// app/Services/Contracts/AuditLogServiceInterface.php
namespace App\Services\Contracts;

use App\Models\AuditLog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

interface AuditLogServiceInterface
{
    public function record(
        string $action,
        ?Model $subject = null,
        array $changesOld = [],
        array $changesNew = [],
        array $meta = [],
        ?string $message = null
    ): AuditLog;

    public function paginate(int $perPage = 50, array $filters = []): LengthAwarePaginator;
}
