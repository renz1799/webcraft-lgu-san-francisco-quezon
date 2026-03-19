<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
/*
|--------------------------------------------------------------------------
| Users
|--------------------------------------------------------------------------
*/
// Repositories
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\EloquentUserRepository;

// Builders
use App\Builders\Contracts\User\UserDatatableRowBuilderInterface; //datatable
use App\Builders\User\UserDatatableRowBuilder; //datatable
use App\Builders\Contracts\User\UserDatatableActionBuilderInterface; //datatable actions
use App\Builders\User\UserDatatableActionBuilder; //datatable actions

/*
|--------------------------------------------------------------------------
| Tasks
|--------------------------------------------------------------------------
*/

// Builders
use App\Builders\Contracts\User\UserTaskReassignOptionBuilderInterface;
use App\Builders\User\UserTaskReassignOptionBuilder;

/*
|--------------------------------------------------------------------------
| Audit Logs
|--------------------------------------------------------------------------
*/

// Repositories
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Repositories\Eloquent\EloquentAuditLogRepository;

// Services
use App\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Services\AuditLogs\AuditLogService;

// Printing
use App\Services\Contracts\AuditLogs\AuditLogPrintServiceInterface;
use App\Services\AuditLogs\AuditLogPrintService;

/*
|--------------------------------------------------------------------------
| Authentication / Login
|--------------------------------------------------------------------------
*/

// Repositories
use App\Repositories\Contracts\LoginDetailRepositoryInterface;
use App\Repositories\Eloquent\EloquentLoginDetailRepository;


// Services
use App\Services\Contracts\AuthServiceInterface;
use App\Services\Auth\AuthService;

// Builders
use App\Builders\Contracts\Login\LoginAttemptLogBuilderInterface;
use App\Builders\Login\LoginAttemptLogBuilder;

/*
|--------------------------------------------------------------------------
| Access Control
|--------------------------------------------------------------------------
*/

// Repositories
use App\Repositories\Contracts\PermissionRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Eloquent\EloquentPermissionRepository;
use App\Repositories\Eloquent\EloquentRoleRepository;

// Services
use App\Services\Contracts\Access\LoginLogServiceInterface;
use App\Services\Contracts\Access\ModuleAccessServiceInterface;
use App\Services\Contracts\Access\PermissionServiceInterface;
use App\Services\Contracts\Access\RoleServiceInterface;
use App\Services\Contracts\Access\UserAccessServiceInterface;
use App\Services\Contracts\Access\UserProfileServiceInterface;
use App\Services\Access\LoginLogService;
use App\Services\Access\ModuleAccessService;
use App\Services\Access\PermissionService;
use App\Services\Access\RoleService;
use App\Services\Access\UserAccessService;
use App\Services\Access\UserProfileService;

//Builders 


/*
|--------------------------------------------------------------------------
| Notifications
|--------------------------------------------------------------------------
*/

// Repositories
use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Repositories\Eloquent\EloquentNotificationRepository;

// Services
use App\Services\Contracts\Notifications\NotificationServiceInterface;
use App\Services\Notifications\NotificationService;

/*
|--------------------------------------------------------------------------
| Tasks
|--------------------------------------------------------------------------
*/

// Repositories
use App\Repositories\Contracts\TaskEventRepositoryInterface;
use App\Repositories\Contracts\TaskRepositoryInterface;
use App\Repositories\Eloquent\EloquentTaskEventRepository;
use App\Repositories\Eloquent\EloquentTaskRepository;

// Services
use App\Services\Contracts\Tasks\TaskServiceInterface;
use App\Services\Contracts\Tasks\TaskShowActionProviderInterface;
use App\Services\Contracts\Tasks\TaskTimelineServiceInterface;
use App\Services\Tasks\TaskService;
use App\Services\Tasks\TaskShowActionProvider;
use App\Services\Tasks\TaskTimelineService;

/*
|--------------------------------------------------------------------------
| Google Drive
|--------------------------------------------------------------------------
*/

// Repositories
use App\Repositories\Contracts\GoogleTokenRepositoryInterface;
use App\Repositories\Eloquent\EloquentGoogleTokenRepository;

// Services
use App\Services\Contracts\GoogleDrive\GoogleDriveGlobalServiceInterface;
use App\Services\Contracts\GoogleDrive\GoogleDriveOAuthServiceInterface;
use App\Services\GoogleDrive\GoogleDriveGlobalService;
use App\Services\GoogleDrive\GoogleDriveOAuthService;

/*
|--------------------------------------------------------------------------
| Theme Preferences
|--------------------------------------------------------------------------
*/

// Repositories
use App\Repositories\Contracts\ThemePreferencesRepositoryInterface;
use App\Repositories\Eloquent\EloquentThemePreferencesRepository;

/*
|--------------------------------------------------------------------------
| Infrastructure / Technical Services
|--------------------------------------------------------------------------
*/

// Services
use App\Services\Contracts\GeocodingServiceInterface;
use App\Services\Contracts\Infrastructure\PdfGeneratorInterface;
use App\Services\Geocoding\PositionstackGeocodingService;
use App\Services\Infrastructure\ChromePdfGenerator;

class CoreServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerRepositories();
        $this->registerApplicationBuilders();
        $this->registerApplicationServices();
        $this->registerInfrastructureServices();
    }

    private function registerRepositories(): void
    {
        $this->bindMany([
            // Audit Logs
            AuditLogRepositoryInterface::class => EloquentAuditLogRepository::class,

            // Authentication / Login
            UserRepositoryInterface::class => EloquentUserRepository::class,
            LoginDetailRepositoryInterface::class => EloquentLoginDetailRepository::class,

            // Access Control
            PermissionRepositoryInterface::class => EloquentPermissionRepository::class,
            RoleRepositoryInterface::class => EloquentRoleRepository::class,

            // Notifications
            NotificationRepositoryInterface::class => EloquentNotificationRepository::class,

            // Tasks
            TaskRepositoryInterface::class => EloquentTaskRepository::class,
            TaskEventRepositoryInterface::class => EloquentTaskEventRepository::class,

            // Google Drive
            GoogleTokenRepositoryInterface::class => EloquentGoogleTokenRepository::class,

            // Theme Preferences
            ThemePreferencesRepositoryInterface::class => EloquentThemePreferencesRepository::class,
        ]);
    }

    private function registerApplicationBuilders(): void
    {
        $this->bindMany([
            // Authentication / Login
            LoginAttemptLogBuilderInterface::class => LoginAttemptLogBuilder::class,

            // Users
            UserDatatableRowBuilderInterface::class => UserDatatableRowBuilder::class,
            UserDatatableActionBuilderInterface::class => UserDatatableActionBuilder::class,
            //Tasks
            UserTaskReassignOptionBuilderInterface::class => UserTaskReassignOptionBuilder::class,
            
        ]);
    }

    private function registerApplicationServices(): void
    {
        $this->bindMany([
            // Audit Logs
            AuditLogServiceInterface::class => AuditLogService::class,
            AuditLogPrintServiceInterface::class => AuditLogPrintService::class,

            // Authentication / Login
            AuthServiceInterface::class => AuthService::class,

            // Access Control
            UserAccessServiceInterface::class => UserAccessService::class,
            PermissionServiceInterface::class => PermissionService::class,
            RoleServiceInterface::class => RoleService::class,
            LoginLogServiceInterface::class => LoginLogService::class,
            UserProfileServiceInterface::class => UserProfileService::class,
            ModuleAccessServiceInterface::class => ModuleAccessService::class,

            // Notifications
            NotificationServiceInterface::class => NotificationService::class,

            // Tasks
            TaskServiceInterface::class => TaskService::class,
            TaskTimelineServiceInterface::class => TaskTimelineService::class,
            TaskShowActionProviderInterface::class => TaskShowActionProvider::class,

            // Google Drive
            GoogleDriveOAuthServiceInterface::class => GoogleDriveOAuthService::class,
            GoogleDriveGlobalServiceInterface::class => GoogleDriveGlobalService::class,
        ], true);
    }

    private function registerInfrastructureServices(): void
    {
        $this->bindMany([
            GeocodingServiceInterface::class => PositionstackGeocodingService::class,
            PdfGeneratorInterface::class => ChromePdfGenerator::class,
        ], true);
    }

    /**
     * @param  array<class-string, class-string>  $map
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