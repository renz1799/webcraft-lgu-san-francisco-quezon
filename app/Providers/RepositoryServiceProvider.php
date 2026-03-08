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
    TaskEventRepositoryInterface,
    GoogleTokenRepositoryInterface
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
    EloquentTaskEventRepository,
    EloquentGoogleTokenRepository
};

// Services
use App\Services\Contracts\{
    AuthServiceInterface,
    GeocodingServiceInterface,
    UserAccessServiceInterface,
    PermissionServiceInterface,
    AuditLogServiceInterface,
    RoleServiceInterface,
    LoginLogServiceInterface,
    TaskServiceInterface,
    TaskShowActionProviderInterface,
    UserProfileServiceInterface,
    GoogleDriveOAuthServiceInterface,
    GoogleDriveGlobalServiceInterface
};
use App\Services\Auth\AuthService;
use App\Services\Geocoding\PositionstackGeocodingService;
use App\Services\Access\UserAccessService;
use App\Services\Access\PermissionService;
use App\Services\Audit\AuditLogService;
use App\Services\Access\RoleService;
use App\Services\Access\LoginLogService;
use App\Services\Tasks\TaskService;
use App\Services\Tasks\TaskShowActionProvider;
use App\Services\Access\UserProfileService;
use App\Services\GoogleDrive\GoogleDriveOAuthService;
use App\Services\GoogleDrive\GoogleDriveGlobalService;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repositories
        $this->bindMany([
            UserRepositoryInterface::class        => EloquentUserRepository::class,
            LoginDetailRepositoryInterface::class => EloquentLoginDetailRepository::class,
            PermissionRepositoryInterface::class  => EloquentPermissionRepository::class,
            AuditLogRepositoryInterface::class    => EloquentAuditLogRepository::class,
            RoleRepositoryInterface::class        => EloquentRoleRepository::class,
            ThemePreferencesRepositoryInterface::class => EloquentThemePreferencesRepository::class,
            NotificationRepositoryInterface::class => EloquentNotificationRepository::class,
            TaskRepositoryInterface::class        => EloquentTaskRepository::class,
            TaskEventRepositoryInterface::class   => EloquentTaskEventRepository::class,
            GoogleTokenRepositoryInterface::class => EloquentGoogleTokenRepository::class,
        ]);

        // Services (singletons by default here)
        $this->bindMany([
            AuthServiceInterface::class           => AuthService::class,
            GeocodingServiceInterface::class      => PositionstackGeocodingService::class,
            UserAccessServiceInterface::class     => UserAccessService::class,
            PermissionServiceInterface::class     => PermissionService::class,
            AuditLogServiceInterface::class       => AuditLogService::class,
            RoleServiceInterface::class           => RoleService::class,
            LoginLogServiceInterface::class       => LoginLogService::class,
            TaskServiceInterface::class           => TaskService::class,
            TaskShowActionProviderInterface::class => TaskShowActionProvider::class,
            UserProfileServiceInterface::class    => UserProfileService::class,
            GoogleDriveOAuthServiceInterface::class => GoogleDriveOAuthService::class,
            GoogleDriveGlobalServiceInterface::class => GoogleDriveGlobalService::class,
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



