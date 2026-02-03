<?php

namespace App\Repositories\Eloquent;

use App\Models\GoogleToken;
use App\Repositories\Contracts\GoogleTokenRepositoryInterface;
use Illuminate\Support\Facades\Crypt;

class EloquentGoogleTokenRepository implements GoogleTokenRepositoryInterface
{
    private const PROVIDER = 'google_drive_global';

    public function upsertForUser(string $userId, array $data): GoogleToken
    {
        // Encrypt sensitive fields
        if (array_key_exists('access_token', $data) && $data['access_token']) {
            $data['access_token'] = Crypt::encryptString($data['access_token']);
        }

        if (array_key_exists('refresh_token', $data) && $data['refresh_token']) {
            $data['refresh_token'] = Crypt::encryptString($data['refresh_token']);
        }

        return GoogleToken::query()->updateOrCreate(
            ['user_id' => $userId, 'provider' => 'google_drive'],
            $data
        );
    }

    public function findForUser(string $userId): ?GoogleToken
    {
        return GoogleToken::query()
            ->where('user_id', $userId)
            ->where('provider', 'google_drive')
            ->first();
    }

        public function upsertGlobal(array $data): GoogleToken
    {
        if (array_key_exists('access_token', $data) && $data['access_token']) {
            $data['access_token'] = Crypt::encryptString($data['access_token']);
        }

        if (array_key_exists('refresh_token', $data) && $data['refresh_token']) {
            $data['refresh_token'] = Crypt::encryptString($data['refresh_token']);
        }

        return GoogleToken::query()->updateOrCreate(
            ['provider' => self::PROVIDER],
            $data + ['user_id' => null]
        );
    }

    public function getGlobal(): ?GoogleToken
    {
        return GoogleToken::query()->where('provider', self::PROVIDER)->first();
    }

    public function deleteGlobal(): void
    {
        GoogleToken::query()->where('provider', self::PROVIDER)->delete();
    }
}
