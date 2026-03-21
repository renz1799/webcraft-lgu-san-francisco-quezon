<?php

namespace App\Core\Builders\Login;

use App\Core\Builders\Contracts\Login\LoginAttemptLogBuilderInterface;

class LoginAttemptLogBuilder implements LoginAttemptLogBuilderInterface
{
    public function build(
        ?string $moduleId,
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
    ): array {
        return [
            'module_id'  => $moduleId,
            'user_id'    => $userId,
            'email'      => $email,
            'ip_address' => $ip,
            'device'     => $userAgent,
            'location'   => $locationUrl,
            'address'    => $address,
            'latitude'   => $latitude,
            'longitude'  => $longitude,
            'success'    => $success,
            'reason'     => $reason,
        ];
    }
}
