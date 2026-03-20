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
use App\Builders\Contracts\User\UserDatatableActionBuilderInterface;
use App\Builders\Contracts\User\UserDatatableRowBuilderInterface;
use App\Builders\Contracts\User\UserTaskReassignOptionBuilderInterface;
use App\Builders\Contracts\Access\PermissionAuditDisplayBuilderInterface;
use App\Builders\Contracts\Access\RoleAuditDisplayBuilderInterface;
use App\Builders\Contracts\Tasks\TaskAdminStatsBuilderInterface;
use App\Builders\Contracts\Tasks\TaskDatatableRowBuilderInterface;
use App\Builders\Contracts\Tasks\TaskReassignmentNoteBuilderInterface;
use App\Builders\User\UserDatatableActionBuilder;
use App\Builders\User\UserDatatableRowBuilder;
use App\Builders\User\UserTaskReassignOptionBuilder;
use App\Builders\Access\PermissionAuditDisplayBuilder;
use App\Builders\Access\RoleAuditDisplayBuilder;
use App\Builders\Tasks\TaskAdminStatsBuilder;
use App\Builders\Tasks\TaskDatatableRowBuilder;
use App\Builders\Tasks\TaskReassignmentNoteBuilder;

/*
|--------------------------------------------------------------------------
| Audit Logs
|--------------------------------------------------------------------------
*/

// Repositories
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Repositories\Eloquent\EloquentAuditLogRepository;

// Builders
use App\Builders\Contracts\AuditLogs\AuditLogDatatableRowBuilderInterface;
use App\Builders\Contracts\AuditLogs\AuditLogMetaBuilderInterface;
use App\Builders\Contracts\AuditLogs\AuditLogPrintReportBuilderInterface;
use App\Builders\AuditLogs\AuditLogDatatableRowBuilder;
use App\Builders\AuditLogs\AuditLogMetaBuilder;
use App\Builders\AuditLogs\AuditLogPrintReportBuilder;

// Services
use App\Services\Contracts\AuditLogs\AuditLogPrintServiceInterface;
use App\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Services\Contracts\AuditLogs\AuditRestoreServiceInterface;
use App\Services\AuditLogs\AuditLogPrintService;
use App\Services\AuditLogs\AuditLogService;
use App\Services\AuditLogs\AuditRestoreService;

/*
|--------------------------------------------------------------------------
| Authentication / Login
|--------------------------------------------------------------------------
*/

// Repositories
use App\Repositories\Contracts\LoginDetailRepositoryInterface;
use App\Repositories\Eloquent\EloquentLoginDetailRepository;

// Services
use App\Services\Contracts\Auth\AuthServiceInterface;
use App\Services\Contracts\Auth\RegisterUserServiceInterface;
use App\Services\Contracts\Auth\RegistrationOptionsServiceInterface;
use App\Services\Auth\AuthService;
use App\Services\Auth\RegisterUserService;
use App\Services\Auth\RegistrationOptionsService;

// Builders
use App\Builders\Contracts\Auth\RegistrationRoleOptionsBuilderInterface;
use App\Builders\Contracts\Login\LoginAttemptLogBuilderInterface;
use App\Builders\Auth\RegistrationRoleOptionsBuilder;
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
use App\Services\Contracts\Access\RoleAssignments\ModuleRoleAssignmentServiceInterface;
use App\Services\Contracts\Access\RoleServiceInterface;
use App\Services\Contracts\Access\UserAccessServiceInterface;
use App\Services\Contracts\Access\UserProfileServiceInterface;
use App\Services\Access\LoginLogService;
use App\Services\Access\ModuleAccessService;
use App\Services\Access\PermissionService;
use App\Services\Access\RoleAssignments\ModuleRoleAssignmentService;
use App\Services\Access\RoleService;
use App\Services\Access\UserAccessService;
use App\Services\Access\UserProfileService;

/*
|--------------------------------------------------------------------------
| Notifications
|--------------------------------------------------------------------------
*/

// Repositories
use App\Repositories\Contracts\NotificationRepositoryInterface;
use App\Repositories\Eloquent\EloquentNotificationRepository;

// Services
use App\Services\Contracts\NotificationServiceInterface;
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
use App\Services\Contracts\TaskReadServiceInterface;
use App\Services\Contracts\TaskServiceInterface;
use App\Services\Contracts\TaskShowActionProviderInterface;
use App\Services\Contracts\TaskTimelineServiceInterface;
use App\Services\Tasks\TaskReadService;
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
            // Audit Logs
            AuditLogDatatableRowBuilderInterface::class => AuditLogDatatableRowBuilder::class,
            AuditLogMetaBuilderInterface::class => AuditLogMetaBuilder::class,
            AuditLogPrintReportBuilderInterface::class => AuditLogPrintReportBuilder::class,

            // Authentication / Login
            LoginAttemptLogBuilderInterface::class => LoginAttemptLogBuilder::class,

            // Authentication / Register
            RegistrationRoleOptionsBuilderInterface::class => RegistrationRoleOptionsBuilder::class,

            // Access Control
            RoleAuditDisplayBuilderInterface::class => RoleAuditDisplayBuilder::class,
            PermissionAuditDisplayBuilderInterface::class => PermissionAuditDisplayBuilder::class,

            // Users
            UserDatatableRowBuilderInterface::class => UserDatatableRowBuilder::class,
            UserDatatableActionBuilderInterface::class => UserDatatableActionBuilder::class,
            UserTaskReassignOptionBuilderInterface::class => UserTaskReassignOptionBuilder::class,

            // Tasks
            TaskDatatableRowBuilderInterface::class => TaskDatatableRowBuilder::class,
            TaskAdminStatsBuilderInterface::class => TaskAdminStatsBuilder::class,
            TaskReassignmentNoteBuilderInterface::class => TaskReassignmentNoteBuilder::class,
        ]);
    }

    private function registerApplicationServices(): void
    {
        $this->bindMany([
            // Audit Logs
            AuditLogServiceInterface::class => AuditLogService::class,
            AuditLogPrintServiceInterface::class => AuditLogPrintService::class,
            AuditRestoreServiceInterface::class => AuditRestoreService::class,

            // Authentication / Login
            AuthServiceInterface::class => AuthService::class,

            // Authentication / Register
            RegisterUserServiceInterface::class => RegisterUserService::class,
            RegistrationOptionsServiceInterface::class => RegistrationOptionsService::class,

            // Access Control
            UserAccessServiceInterface::class => UserAccessService::class,
            PermissionServiceInterface::class => PermissionService::class,
            RoleServiceInterface::class => RoleService::class,
            LoginLogServiceInterface::class => LoginLogService::class,
            UserProfileServiceInterface::class => UserProfileService::class,
            ModuleAccessServiceInterface::class => ModuleAccessService::class,
            ModuleRoleAssignmentServiceInterface::class => ModuleRoleAssignmentService::class,

            // Notifications
            NotificationServiceInterface::class => NotificationService::class,

            // Tasks
            TaskReadServiceInterface::class => TaskReadService::class,
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
