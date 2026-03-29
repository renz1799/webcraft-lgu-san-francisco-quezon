<?php

namespace App\Core\Services\Contracts\Access;

use App\Core\Models\User;

interface UserProfileServiceInterface
{
    public function getProfileData(User $user): array;

    public function updateProfile(User $user, array $data): array;

    public function updatePassword(User $user, array $data): void;
}
