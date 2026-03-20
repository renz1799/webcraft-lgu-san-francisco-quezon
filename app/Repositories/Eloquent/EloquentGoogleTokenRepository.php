<?php

namespace App\Repositories\Eloquent;

use App\Models\GoogleToken;
use App\Repositories\Contracts\GoogleTokenRepositoryInterface;
use Illuminate\Support\Facades\Crypt;

class EloquentGoogleTokenRepository implements GoogleTokenRepositoryInterface
{
    private const PROVIDER = 'google_drive';

    public function upsertForContext(string $moduleId, string $departmentId, array $data): GoogleToken
    {
        return GoogleToken::query()->updateOrCreate(
            [
                'module_id' => $moduleId,
                'department_id' => $departmentId,
                'provider' => self::PROVIDER,
            ],
            $this->encryptSensitiveFields($data),
        );
    }

    public function findForContext(string $moduleId, string $departmentId): ?GoogleToken
    {
        return GoogleToken::query()
            ->where('module_id', $moduleId)
            ->where('department_id', $departmentId)
            ->where('provider', self::PROVIDER)
            ->first();
    }

    public function deleteForContext(string $moduleId, string $departmentId): void
    {
        GoogleToken::query()
            ->where('module_id', $moduleId)
            ->where('department_id', $departmentId)
            ->where('provider', self::PROVIDER)
            ->delete();
    }

    private function encryptSensitiveFields(array $data): array
    {
        if (array_key_exists('access_token', $data) && $data['access_token']) {
            $data['access_token'] = Crypt::encryptString((string) $data['access_token']);
        }

        if (array_key_exists('refresh_token', $data) && $data['refresh_token']) {
            $data['refresh_token'] = Crypt::encryptString((string) $data['refresh_token']);
        }

        return $data;
    }
}
