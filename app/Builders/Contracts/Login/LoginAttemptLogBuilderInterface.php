<?php

namespace App\Builders\Contracts\Login;

interface LoginAttemptLogBuilderInterface
{
    public function build(
        ?string $userId,
        string $email,
        string $ip,
        ?string $userAgent,
        ?string $locationUrl,
        ?string $address,
        mixed $latitude,
        mixed $longitude,
        bool $success,
        string $reason,
    ): array;
}