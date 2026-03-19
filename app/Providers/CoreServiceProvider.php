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

// Application / Module Services
use App\Services\Contracts\{
    AuthServiceInterface,
    GeocodingServiceInterface,
    UserAccessServiceInterface,
    PermissionServiceInterface,
    AuditLogServiceInterface,
    RoleServiceInterface,
    LoginLogServiceInterface,
    NotificationServiceInterface,
    TaskServiceInterface,
    TaskTimelineServiceInterface,
    TaskShowActionProviderInterface,
    UserProfileServiceInterface,
    GoogleDriveOAuthServiceInterface,
    GoogleDriveGlobalServiceInterface,
    ModuleAccessServiceInterface
};
use App\Services\Auth\AuthService;
use App\Services\Geocoding\PositionstackGeocodingService;
use App\Services\Access\UserAccessService;
use App\Services\Access\PermissionService;
use App\Services\Audit\AuditLogService;
use App\Services\Access\RoleService;
use App\Services\Access\LoginLogService;
use App\Services\Notifications\NotificationService;
use App\Services\Tasks\TaskService;
use App\Services\Tasks\TaskTimelineService;
use App\Services\Tasks\TaskShowActionProvider;
use App\Services\Access\UserProfileService;
use App\Services\GoogleDrive\GoogleDriveOAuthService;
use App\Services\GoogleDrive\GoogleDriveGlobalService;

// Print / Report Services
use App\Services\Contracts\AuditLogs\AuditLogPrintServiceInterface;
use App\Services\AuditLogs\AuditLogPrintService;

// Infrastructure Services
use App\Services\Contracts\Infrastructure\PdfGeneratorInterface;
use App\Services\Infrastructure\ChromePdfGenerator;

use App\Services\Access\ModuleAccessService;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerRepositories();
        $this->registerApplicationServices();
        $this->registerInfrastructureServices();
    }

    private function registerRepositories(): void
    {
        $this->bindMany([
            UserRepositoryInterface::class => EloquentUserRepository::class,
            LoginDetailRepositoryInterface::class => EloquentLoginDetailRepository::class,
            PermissionRepositoryInterface::class => EloquentPermissionRepository::class,
            AuditLogRepositoryInterface::class => EloquentAuditLogRepository::class,
            RoleRepositoryInterface::class => EloquentRoleRepository::class,
            ThemePreferencesRepositoryInterface::class => EloquentThemePreferencesRepository::class,
            NotificationRepositoryInterface::class => EloquentNotificationRepository::class,
            TaskRepositoryInterface::class => EloquentTaskRepository::class,
            TaskEventRepositoryInterface::class => EloquentTaskEventRepository::class,
            GoogleTokenRepositoryInterface::class => EloquentGoogleTokenRepository::class,
        ]);
    }

    private function registerApplicationServices(): void
    {
        $this->bindMany([
            AuthServiceInterface::class => AuthService::class,
            GeocodingServiceInterface::class => PositionstackGeocodingService::class,
            UserAccessServiceInterface::class => UserAccessService::class,
            PermissionServiceInterface::class => PermissionService::class,
            AuditLogServiceInterface::class => AuditLogService::class,
            RoleServiceInterface::class => RoleService::class,
            LoginLogServiceInterface::class => LoginLogService::class,
            NotificationServiceInterface::class => NotificationService::class,
            TaskServiceInterface::class => TaskService::class,
            TaskTimelineServiceInterface::class => TaskTimelineService::class,
            TaskShowActionProviderInterface::class => TaskShowActionProvider::class,
            UserProfileServiceInterface::class => UserProfileService::class,
            GoogleDriveOAuthServiceInterface::class => GoogleDriveOAuthService::class,
            GoogleDriveGlobalServiceInterface::class => GoogleDriveGlobalService::class,

            // Print / export use cases
            AuditLogPrintServiceInterface::class => AuditLogPrintService::class,

            ModuleAccessServiceInterface::class => ModuleAccessService::class,
        ], true);
    }

    private function registerInfrastructureServices(): void
    {
        $this->bindMany([
            PdfGeneratorInterface::class => ChromePdfGenerator::class,
        ], true);
    }

    /**
     * Bind a map of abstractions to concrete classes.
     *
     * @param array<class-string, class-string> $map
     */
    private function bindMany(array $map, bool $asSingleton = false): void
    {
        foreach ($map as $abstract => $concrete) {
            if ($asSingleton) {
                $this->app->singleton($abstract, $concrete);
                continue;
            }

            $this->app->bind($abstract, $concrete);
        }
    }
}