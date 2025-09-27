<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Repositories
use App\Repositories\Contracts\{
    UserRepositoryInterface,
    LoginDetailRepositoryInterface,
    PermissionRepositoryInterface,
    AuditLogRepositoryInterface
};
use App\Repositories\Eloquent\{
    EloquentUserRepository,
    EloquentLoginDetailRepository,
    EloquentPermissionRepository,
    EloquentAuditLogRepository
};

// Services
use App\Services\Contracts\{
    AuthServiceInterface,
    GeocodingServiceInterface,
    UserAccessServiceInterface,
    PermissionServiceInterface,
    AuditLogServiceInterface
};
use App\Services\Auth\AuthService;
use App\Services\Geocoding\PositionstackGeocodingService;
use App\Services\UserAccessService;
use App\Services\PermissionService;
use App\Services\Audit\AuditLogService;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repositories
        $this->bindMany([
            UserRepositoryInterface::class        => EloquentUserRepository::class,
            LoginDetailRepositoryInterface::class => EloquentLoginDetailRepository::class,
            PermissionRepositoryInterface::class  => EloquentPermissionRepository::class,
            AuditLogRepositoryInterface::class  => EloquentAuditLogRepository::class,
        ]);

        // Services (singletons by default here)
        $this->bindMany([
            AuthServiceInterface::class        => AuthService::class,
            GeocodingServiceInterface::class   => PositionstackGeocodingService::class,
            UserAccessServiceInterface::class  => UserAccessService::class,
            PermissionServiceInterface::class  => PermissionService::class,
            AuditLogServiceInterface::class  => AuditLogService::class,
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
