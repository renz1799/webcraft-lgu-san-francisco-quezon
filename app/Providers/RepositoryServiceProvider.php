<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Repositories
use App\Repositories\Contracts\{
    UserRepositoryInterface,
    LoginDetailRepositoryInterface,
    PermissionRepositoryInterface
};
use App\Repositories\Eloquent\{
    EloquentUserRepository,
    EloquentLoginDetailRepository,
    EloquentPermissionRepository
};

// Services
use App\Services\Contracts\{
    AuthServiceInterface,
    GeocodingServiceInterface,
    UserAccessServiceInterface,
    PermissionServiceInterface
};
use App\Services\Auth\AuthService;
use App\Services\Geocoding\PositionstackGeocodingService;
use App\Services\UserAccessService;
use App\Services\PermissionService;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repositories
        $this->bindMany([
            UserRepositoryInterface::class        => EloquentUserRepository::class,
            LoginDetailRepositoryInterface::class => EloquentLoginDetailRepository::class,
            PermissionRepositoryInterface::class  => EloquentPermissionRepository::class,
        ]);

        // Services (singletons by default here)
        $this->bindMany([
            AuthServiceInterface::class        => AuthService::class,
            GeocodingServiceInterface::class   => PositionstackGeocodingService::class,
            UserAccessServiceInterface::class  => UserAccessService::class,
            PermissionServiceInterface::class  => PermissionService::class,
        ], true);
    }

    /**
     * Bind a map of abstractions to concretes.
     *
     * @param  array<class-string, class-string>  $map
     * @param  bool  $asSingleton
     */
    protected function bindMany(array $map, bool $asSingleton = false): void
    {
        foreach ($map as $abstract => $concrete) {
            $asSingleton
                ? $this->app->singleton($abstract, $concrete)
                : $this->app->bind($abstract, $concrete);
        }
    }
}
