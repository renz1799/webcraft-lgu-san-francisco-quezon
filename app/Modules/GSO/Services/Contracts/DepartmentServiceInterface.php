<?php

namespace App\Modules\GSO\Services\Contracts;

use App\Core\Models\Department;
use Illuminate\Support\Collection;

interface DepartmentServiceInterface
{
    public function datatable(array $params): array;

    public function optionsForSelect(): Collection;

    public function create(string $actorUserId, array $data): Department;

    public function update(string $actorUserId, string $departmentId, array $data): Department;

    public function delete(string $actorUserId, string $departmentId): void;

    public function restore(string $actorUserId, string $departmentId): void;
}
