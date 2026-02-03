<?php

namespace App\Repositories\Contracts;

use App\Models\GoogleToken;

interface GoogleTokenRepositoryInterface
{
    public function upsertForUser(string $userId, array $data): GoogleToken;

    public function findForUser(string $userId): ?GoogleToken;

    public function upsertGlobal(array $data): GoogleToken;

    public function getGlobal(): ?GoogleToken;

    public function deleteGlobal(): void;
}
