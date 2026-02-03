<?php

namespace App\Repositories\Contracts;

use App\Models\GoogleToken;

interface GoogleTokenRepositoryInterface
{
    public function upsertForUser(string $userId, array $data): GoogleToken;

    public function findForUser(string $userId): ?GoogleToken;
}
