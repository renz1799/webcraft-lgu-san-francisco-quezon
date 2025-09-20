<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\LoginDetailRepositoryInterface;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Repositories\Eloquent\EloquentLoginDetailRepository;

use App\Services\Contracts\AuthServiceInterface;
use App\Services\Contracts\GeocodingServiceInterface;
use App\Services\Auth\AuthService;
use App\Services\Geocoding\PositionstackGeocodingService;
use App\Services\Contracts\UserPermissionsServiceInterface;
use App\Services\UserPermissionsService;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repositories
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(LoginDetailRepositoryInterface::class, EloquentLoginDetailRepository::class);

        // Services
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(GeocodingServiceInterface::class, PositionstackGeocodingService::class);
        $this->app->bind(UserPermissionsServiceInterface::class, UserPermissionsService::class);
    }
}
