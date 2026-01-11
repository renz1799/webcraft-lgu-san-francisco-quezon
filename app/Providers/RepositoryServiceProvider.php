<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Repositories
use App\Repositories\Contracts\{
    UserRepositoryInterface,
    LoginDetailRepositoryInterface,
    PermissionRepositoryInterface,
    AuditLogRepositoryInterface,
    RoleRepositoryInterface,
    ThemePreferencesRepositoryInterface,
    NotificationRepositoryInterface,
    TaskRepositoryInterface,
    TaskEventRepositoryInterface
};
use App\Repositories\Eloquent\{
    EloquentUserRepository,
    EloquentLoginDetailRepository,
    EloquentPermissionRepository,
    EloquentAuditLogRepository,
    EloquentRoleRepository,
    EloquentThemePreferencesRepository,
    EloquentNotificationRepository,
    EloquentTaskRepository,
    EloquentTaskEventRepository
};

// Services
use App\Services\Contracts\{
    AuthServiceInterface,
    GeocodingServiceInterface,
    UserAccessServiceInterface,
    PermissionServiceInterface,
    AuditLogServiceInterface,
    AuditLogTableServiceInterface,
    RoleServiceInterface,
    LoginLogServiceInterface,
    TaskServiceInterface,
    UserProfileServiceInterface

    

};
use App\Services\Auth\AuthService;
use App\Services\Geocoding\PositionstackGeocodingService;
use App\Services\UserAccessService;
use App\Services\PermissionService;
use App\Services\Audit\AuditLogService;
use App\Services\Audit\AuditLogTableService;
use App\Services\RoleService;
use App\Services\LoginLogService;
use App\Services\Tasks\TaskService;
use App\Services\UserProfileService;



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
            RoleRepositoryInterface::class  => EloquentRoleRepository::class,
            ThemePreferencesRepositoryInterface::class => EloquentThemePreferencesRepository::class,
            NotificationRepositoryInterface::class => EloquentNotificationRepository::class,
            TaskRepositoryInterface::class => EloquentTaskRepository::class,
            TaskEventRepositoryInterface::class => EloquentTaskEventRepository::class,
        ]);

        // Services (singletons by default here)
        $this->bindMany([
            AuthServiceInterface::class        => AuthService::class,
            GeocodingServiceInterface::class   => PositionstackGeocodingService::class,
            UserAccessServiceInterface::class  => UserAccessService::class,
            PermissionServiceInterface::class  => PermissionService::class,
            AuditLogServiceInterface::class  => AuditLogService::class,
            AuditLogTableServiceInterface::class  => AuditLogTableService::class,
            RoleServiceInterface::class  => RoleService::class,
            LoginLogServiceInterface::class  => LoginLogService::class,
            TaskServiceInterface::class  => TaskService::class,
            UserProfileServiceInterface::class  => UserProfileService::class,
           
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
