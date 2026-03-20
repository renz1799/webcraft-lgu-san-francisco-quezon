<?php

namespace App\Repositories\Contracts;

use App\Models\GoogleToken;

interface GoogleTokenRepositoryInterface
{
    public function upsertForContext(string $moduleId, string $departmentId, array $data): GoogleToken;

    public function findForContext(string $moduleId, string $departmentId): ?GoogleToken;

    public function deleteForContext(string $moduleId, string $departmentId): void;
}
