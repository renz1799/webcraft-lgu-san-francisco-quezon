<?php

namespace App\Services\Contracts;

use App\Models\User;

interface UserProfileServiceInterface
{
    public function getMailSettingsData(User $user): array;

    public function updateProfile(User $user, array $data): void;

    public function updatePassword(User $user, array $data): void;
}
