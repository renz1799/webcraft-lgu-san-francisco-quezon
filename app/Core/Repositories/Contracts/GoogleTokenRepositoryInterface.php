<?php

namespace App\Core\Repositories\Contracts;

use App\Core\Models\GoogleToken;

interface GoogleTokenRepositoryInterface
{
    public function upsertForContext(string $moduleId, string $departmentId, array $data): GoogleToken;

    public function findForContext(string $moduleId, string $departmentId): ?GoogleToken;

    public function deleteForContext(string $moduleId, string $departmentId): void;
}
