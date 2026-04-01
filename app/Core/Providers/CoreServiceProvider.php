<?php

namespace App\Core\Providers;

use Illuminate\Support\ServiceProvider;

/*
|--------------------------------------------------------------------------
| Users
|--------------------------------------------------------------------------
*/

// Repositories
use App\Core\Repositories\Contracts\AccountablePersonRepositoryInterface;
use App\Core\Repositories\Contracts\UserIdentityChangeRequestRepositoryInterface;
use App\Core\Repositories\Contracts\UserRepositoryInterface;
use App\Core\Repositories\Eloquent\EloquentAccountablePersonRepository;
use App\Core\Repositories\Eloquent\EloquentUserIdentityChangeRequestRepository;
use App\Core\Repositories\Eloquent\EloquentUserRepository;

// Builders
use App\Core\Builders\AccountablePersons\AccountablePersonDatatableRowBuilder;
use App\Core\Builders\Contracts\AccountablePersons\AccountablePersonDatatableRowBuilderInterface;
use App\Core\Builders\Contracts\User\UserDatatableActionBuilderInterface;
use App\Core\Builders\Contracts\User\UserDatatableRowBuilderInterface;
use App\Core\Builders\Contracts\User\UserPlatformAccessOverviewBuilderInterface;
use App\Core\Builders\Contracts\Access\PermissionAuditDisplayBuilderInterface;
use App\Core\Builders\Contracts\Access\RoleAuditDisplayBuilderInterface;
use App\Core\Builders\Contracts\Access\UserIdentityChangeRequestAuditDisplayBuilderInterface;
use App\Core\Builders\Contracts\GoogleDrive\GoogleDriveFileMetadataBuilderInterface;
use App\Core\Builders\Contracts\GoogleDrive\GoogleDriveFolderNameSanitizerInterface;
use App\Core\Builders\User\UserDatatableActionBuilder;
use App\Core\Builders\User\UserDatatableRowBuilder;
use App\Core\Builders\User\UserPlatformAccessOverviewBuilder;
use App\Core\Builders\Access\PermissionAuditDisplayBuilder;
use App\Core\Builders\Access\RoleAuditDisplayBuilder;
use App\Core\Builders\Access\UserIdentityChangeRequestAuditDisplayBuilder;
use App\Core\Builders\GoogleDrive\GoogleDriveFileMetadataBuilder;
use App\Core\Builders\GoogleDrive\GoogleDriveFolderNameSanitizer;

/*
|--------------------------------------------------------------------------
| Accountable Persons
|--------------------------------------------------------------------------
*/

// Services
use App\Core\Services\AccountablePersons\AccountablePersonService;
use App\Core\Services\Contracts\AccountablePersons\AccountablePersonServiceInterface;

/*
|--------------------------------------------------------------------------
| Audit Logs
|--------------------------------------------------------------------------
*/

// Repositories
use App\Core\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Core\Repositories\Eloquent\EloquentAuditLogRepository;

// Builders
use App\Core\Builders\Contracts\AuditLogs\AuditLogDatatableRowBuilderInterface;
use App\Core\Builders\Contracts\AuditLogs\AuditLogMetaBuilderInterface;
use App\Core\Builders\Contracts\AuditLogs\AuditLogPrintReportBuilderInterface;
use App\Core\Builders\AuditLogs\AuditLogDatatableRowBuilder;
use App\Core\Builders\AuditLogs\AuditLogMetaBuilder;
use App\Core\Builders\AuditLogs\AuditLogPrintReportBuilder;

// Services
use App\Core\Services\Contracts\AuditLogs\AuditLogPrintServiceInterface;
use App\Core\Services\Contracts\AuditLogs\AuditLogServiceInterface;
use App\Core\Services\Contracts\AuditLogs\AuditRestoreServiceInterface;
use App\Core\Services\AuditLogs\AuditLogPrintService;
use App\Core\Services\AuditLogs\AuditLogService;
use App\Core\Services\AuditLogs\AuditRestoreService;

/*
|--------------------------------------------------------------------------
| Authentication / Login
|--------------------------------------------------------------------------
*/

// Repositories
use App\Core\Repositories\Contracts\LoginDetailRepositoryInterface;
use App\Core\Repositories\Eloquent\EloquentLoginDetailRepository;

// Services
use App\Core\Services\Contracts\Auth\AuthServiceInterface;
use App\Core\Services\Contracts\Auth\RegisterUserServiceInterface;
use App\Core\Services\Contracts\Auth\RegistrationOptionsServiceInterface;
use App\Core\Services\Auth\AuthService;
use App\Core\Services\Auth\RegisterUserService;
use App\Core\Services\Auth\RegistrationOptionsService;

// Builders
use App\Core\Builders\Contracts\Auth\RegistrationRoleOptionsBuilderInterface;
use App\Core\Builders\Contracts\Login\LoginAttemptLogBuilderInterface;
use App\Core\Builders\Auth\RegistrationRoleOptionsBuilder;
use App\Core\Builders\Login\LoginAttemptLogBuilder;

/*
|--------------------------------------------------------------------------
| Access Control
|--------------------------------------------------------------------------
*/

// Repositories
use App\Core\Repositories\Contracts\PermissionRepositoryInterface;
use App\Core\Repositories\Contracts\RoleRepositoryInterface;
use App\Core\Repositories\Eloquent\EloquentPermissionRepository;
use App\Core\Repositories\Eloquent\EloquentRoleRepository;

// Services
use App\Core\Services\Contracts\Access\LoginLogServiceInterface;
use App\Core\Services\Contracts\Access\CoreUserOnboardingServiceInterface;
use App\Core\Services\Contracts\Access\ModuleAccessServiceInterface;
use App\Core\Services\Contracts\Access\ModuleDepartmentResolverInterface;
use App\Core\Services\Contracts\Access\ModuleUserOnboardingServiceInterface;
use App\Core\Services\Contracts\Access\OnboardingCredentialNotificationServiceInterface;
use App\Core\Services\Contracts\Access\PermissionServiceInterface;
use App\Core\Services\Contracts\Access\RoleAssignments\ModuleRoleAssignmentServiceInterface;
use App\Core\Services\Contracts\Access\RoleServiceInterface;
use App\Core\Services\Contracts\Access\UserModuleDepartmentResolverInterface;
use App\Core\Services\Contracts\Access\UserAccessServiceInterface;
use App\Core\Services\Contracts\Access\UserIdentityChangeRequestServiceInterface;
use App\Core\Services\Contracts\Access\UserProfileServiceInterface;
use App\Core\Services\Access\LoginLogService;
use App\Core\Services\Access\CoreUserOnboardingService;
use App\Core\Services\Access\ModuleAccessService;
use App\Core\Services\Access\ModuleDepartmentResolver;
use App\Core\Services\Access\ModuleUserOnboardingService;
use App\Core\Services\Access\OnboardingCredentialNotificationService;
use App\Core\Services\Access\PermissionService;
use App\Core\Services\Access\RoleAssignments\ModuleRoleAssignmentService;
use App\Core\Services\Access\RoleService;
use App\Core\Services\Access\UserModuleDepartmentResolver;
use App\Core\Services\Access\UserAccessService;
use App\Core\Services\Access\UserIdentityChangeRequestService;
use App\Core\Services\Access\UserProfileService;

/*
|--------------------------------------------------------------------------
| Notifications
|--------------------------------------------------------------------------
*/

// Repositories
use App\Core\Repositories\Contracts\NotificationRepositoryInterface;
use App\Core\Repositories\Eloquent\EloquentNotificationRepository;

// Services
use App\Core\Services\Contracts\Notifications\NotificationServiceInterface;
use App\Core\Services\Notifications\NotificationService;

/*
|--------------------------------------------------------------------------
| Google Drive
|--------------------------------------------------------------------------
*/

// Repositories
use App\Core\Repositories\Contracts\GoogleTokenRepositoryInterface;
use App\Core\Repositories\Eloquent\EloquentGoogleTokenRepository;

// Services
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveClientFactoryInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveConnectionServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFileServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveFolderServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveModuleStorageSettingsServiceInterface;
use App\Core\Services\Contracts\GoogleDrive\GoogleDriveSettingsProviderInterface;
use App\Core\Services\GoogleDrive\GoogleDriveClientFactory;
use App\Core\Services\GoogleDrive\GoogleDriveConnectionService;
use App\Core\Services\GoogleDrive\GoogleDriveFileService;
use App\Core\Services\GoogleDrive\GoogleDriveFolderService;
use App\Core\Services\GoogleDrive\GoogleDriveModuleStorageSettingsService;
use App\Core\Services\GoogleDrive\GoogleDriveSettingsProvider;

/*
|--------------------------------------------------------------------------
| Theme Preferences
|--------------------------------------------------------------------------
*/

// Repositories
use App\Core\Repositories\Contracts\ThemePreferencesRepositoryInterface;
use App\Core\Repositories\Eloquent\EloquentThemePreferencesRepository;

/*
|--------------------------------------------------------------------------
| Infrastructure / Technical Services
|--------------------------------------------------------------------------
*/

// Services
use App\Core\Services\Contracts\Geocoding\GeocodingServiceInterface;
use App\Core\Services\Contracts\Infrastructure\PdfGeneratorInterface;
use App\Core\Services\Contracts\Print\PrintConfigLoaderInterface;
use App\Core\Services\Geocoding\PositionstackGeocodingService;
use App\Core\Services\Infrastructure\ChromePdfGenerator;
use App\Core\Services\Infrastructure\HybridPdfGenerator;
use App\Core\Services\Print\PrintConfigLoaderService;

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
            // Accountable Persons
            AccountablePersonRepositoryInterface::class => EloquentAccountablePersonRepository::class,
            UserIdentityChangeRequestRepositoryInterface::class => EloquentUserIdentityChangeRequestRepository::class,

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

            // Google Drive
            GoogleTokenRepositoryInterface::class => EloquentGoogleTokenRepository::class,

            // Theme Preferences
            ThemePreferencesRepositoryInterface::class => EloquentThemePreferencesRepository::class,
        ]);
    }

    private function registerApplicationBuilders(): void
    {
        $this->bindMany([
            // Accountable Persons
            AccountablePersonDatatableRowBuilderInterface::class => AccountablePersonDatatableRowBuilder::class,

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
            UserIdentityChangeRequestAuditDisplayBuilderInterface::class => UserIdentityChangeRequestAuditDisplayBuilder::class,

            // Users
            UserDatatableRowBuilderInterface::class => UserDatatableRowBuilder::class,
            UserDatatableActionBuilderInterface::class => UserDatatableActionBuilder::class,
            UserPlatformAccessOverviewBuilderInterface::class => UserPlatformAccessOverviewBuilder::class,

            // Google Drive
            GoogleDriveFileMetadataBuilderInterface::class => GoogleDriveFileMetadataBuilder::class,
            GoogleDriveFolderNameSanitizerInterface::class => GoogleDriveFolderNameSanitizer::class,
        ]);
    }

    private function registerApplicationServices(): void
    {
        $this->bindMany([
            // Accountable Persons
            AccountablePersonServiceInterface::class => AccountablePersonService::class,

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
            CoreUserOnboardingServiceInterface::class => CoreUserOnboardingService::class,
            ModuleUserOnboardingServiceInterface::class => ModuleUserOnboardingService::class,
            OnboardingCredentialNotificationServiceInterface::class => OnboardingCredentialNotificationService::class,
            UserProfileServiceInterface::class => UserProfileService::class,
            UserIdentityChangeRequestServiceInterface::class => UserIdentityChangeRequestService::class,
            ModuleAccessServiceInterface::class => ModuleAccessService::class,
            ModuleDepartmentResolverInterface::class => ModuleDepartmentResolver::class,
            UserModuleDepartmentResolverInterface::class => UserModuleDepartmentResolver::class,
            ModuleRoleAssignmentServiceInterface::class => ModuleRoleAssignmentService::class,

            // Notifications
            NotificationServiceInterface::class => NotificationService::class,

            // Google Drive
            GoogleDriveSettingsProviderInterface::class => GoogleDriveSettingsProvider::class,
            GoogleDriveClientFactoryInterface::class => GoogleDriveClientFactory::class,
            GoogleDriveConnectionServiceInterface::class => GoogleDriveConnectionService::class,
            GoogleDriveFolderServiceInterface::class => GoogleDriveFolderService::class,
            GoogleDriveFileServiceInterface::class => GoogleDriveFileService::class,
            GoogleDriveModuleStorageSettingsServiceInterface::class => GoogleDriveModuleStorageSettingsService::class,

            // Print
            PrintConfigLoaderInterface::class => PrintConfigLoaderService::class,
        ], true);
    }

    private function registerInfrastructureServices(): void
    {
        $this->bindMany([
            GeocodingServiceInterface::class => PositionstackGeocodingService::class,
            PdfGeneratorInterface::class => HybridPdfGenerator::class,
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
