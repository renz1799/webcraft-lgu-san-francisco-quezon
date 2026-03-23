# Core Module Structure Map

## Purpose

This document defines the structure baseline for separating:

Core platform code
Module business code

Use this document as the placement map for the current codebase and as the migration guide for future modules:

```text
app/
  Core/
  Modules/
```

This is a placement and ownership guide.

Core and the current business modules should follow this baseline.

Future modules should mirror the same structure instead of reintroducing top-level drift.

---

# Target Direction

Core contains platform capabilities.

Modules contain business applications.

Target shape:

```text
app/
  Core/
    Builders/
      Tasks/
        Contracts/
    Data/
    Http/
      Controllers/
        Tasks/
      Requests/
        Tasks/
      Middleware/
      View/
    Models/
      Tasks/
    Policies/
      Tasks/
    Providers/
    Repositories/
      Tasks/
      Contracts/
      Eloquent/
    Services/
      Tasks/
      Contracts/
    Support/

  Modules/
    DTS/
    GSO/

resources/
  core/
    js/
      tasks/
    views/
      tasks/
```

This structure keeps platform concerns, shared capabilities, and business concerns visibly separate.

---

# Core Target Map

## Core Support

Move to:

```text
app/Core/Support/
```

Current candidates:

`app/Support/CurrentContext.php`
`app/Support/AuditRequestContextResolver.php`

## Core Audit Logs

Move to:

```text
app/Core/Services/AuditLogs/
app/Core/Services/Contracts/AuditLogs/
app/Core/Builders/AuditLogs/
app/Core/Builders/Contracts/AuditLogs/
app/Core/Repositories/Contracts/
app/Core/Repositories/Eloquent/
app/Core/Models/
app/Core/Http/Controllers/AuditLogs/
app/Core/Http/Requests/AuditLogs/
app/Core/Data/AuditLogs/
```

Current candidates:

`app/Services/AuditLogs/AuditLogService.php`
`app/Services/AuditLogs/AuditLogPrintService.php`
`app/Services/AuditLogs/AuditRestoreService.php`
`app/Services/Contracts/AuditLogs/AuditLogServiceInterface.php`
`app/Services/Contracts/AuditLogs/AuditLogPrintServiceInterface.php`
`app/Services/Contracts/AuditLogs/AuditRestoreServiceInterface.php`
`app/Builders/AuditLogs/AuditLogDatatableRowBuilder.php`
`app/Builders/AuditLogs/AuditLogMetaBuilder.php`
`app/Builders/AuditLogs/AuditLogPrintReportBuilder.php`
`app/Builders/Contracts/AuditLogs/AuditLogDatatableRowBuilderInterface.php`
`app/Builders/Contracts/AuditLogs/AuditLogMetaBuilderInterface.php`
`app/Builders/Contracts/AuditLogs/AuditLogPrintReportBuilderInterface.php`
`app/Repositories/Contracts/AuditLogRepositoryInterface.php`
`app/Repositories/Eloquent/EloquentAuditLogRepository.php`
`app/Models/AuditLog.php`
`app/Http/Controllers/AuditLogs/AuditLogController.php`
`app/Http/Controllers/AuditLogs/AuditLogPrintController.php`
`app/Http/Controllers/AuditLogs/AuditRestoreController.php`
`app/Http/Requests/AuditLogs/AuditLogsDataRequest.php`
`app/Http/Requests/AuditLogs/AuditLogPrintRequest.php`
`app/Http/Requests/AuditLogs/RestoreSubjectRequest.php`
`app/Data/AuditLogs/AuditLogPrintData.php`

## Core Notifications

Move to:

```text
app/Core/Services/Notifications/
app/Core/Services/Contracts/Notifications/
app/Core/Repositories/Contracts/
app/Core/Repositories/Eloquent/
app/Core/Models/
app/Core/Http/Controllers/Notifications/
```

Current candidates:

`app/Services/Notifications/NotificationService.php`
`app/Services/Contracts/Notifications/NotificationServiceInterface.php`
`app/Repositories/Contracts/NotificationRepositoryInterface.php`
`app/Repositories/Eloquent/EloquentNotificationRepository.php`
`app/Models/Notification.php`
`app/Http/Controllers/Notifications/NotificationController.php`

## Core Authentication And Access

Move to:

```text
app/Core/Services/Auth/
app/Core/Services/Contracts/Auth/
app/Core/Services/Access/
app/Core/Services/Contracts/Access/
app/Core/Builders/Auth/
app/Core/Builders/Access/
app/Core/Builders/Login/
app/Core/Builders/Contracts/Auth/
app/Core/Builders/Contracts/Access/
app/Core/Builders/Contracts/Login/
app/Core/Repositories/Contracts/
app/Core/Repositories/Eloquent/
app/Core/Models/
app/Core/Policies/
app/Core/Http/Controllers/Auth/
app/Core/Http/Controllers/Access/
app/Core/Http/Controllers/Profile/
app/Core/Http/Controllers/Logs/
app/Core/Http/Requests/Auth/
app/Core/Http/Requests/Users/
app/Core/Http/Requests/Roles/
app/Core/Http/Requests/Permissions/
app/Core/Http/Requests/Logs/
app/Core/Data/Auth/
```

Current candidates:

`app/Services/Auth/AuthService.php`
`app/Services/Auth/RegisterUserService.php`
`app/Services/Auth/RegistrationOptionsService.php`
`app/Services/Access/LoginLogService.php`
`app/Services/Access/ModuleAccessService.php`
`app/Services/Access/PermissionService.php`
`app/Services/Access/RoleService.php`
`app/Services/Access/UserAccessService.php`
`app/Services/Access/UserProfileService.php`
`app/Services/Access/RoleAssignments/ModuleRoleAssignmentService.php`
`app/Services/Contracts/Auth/*`
`app/Services/Contracts/Access/*`
`app/Builders/Auth/RegistrationRoleOptionsBuilder.php`
`app/Builders/Access/PermissionAuditDisplayBuilder.php`
`app/Builders/Access/RoleAuditDisplayBuilder.php`
`app/Builders/Login/LoginAttemptLogBuilder.php`
`app/Builders/Contracts/Auth/*`
`app/Builders/Contracts/Access/*`
`app/Builders/Contracts/Login/*`
`app/Repositories/Contracts/UserRepositoryInterface.php`
`app/Repositories/Contracts/RoleRepositoryInterface.php`
`app/Repositories/Contracts/PermissionRepositoryInterface.php`
`app/Repositories/Contracts/LoginDetailRepositoryInterface.php`
`app/Repositories/Eloquent/EloquentUserRepository.php`
`app/Repositories/Eloquent/EloquentRoleRepository.php`
`app/Repositories/Eloquent/EloquentPermissionRepository.php`
`app/Repositories/Eloquent/EloquentLoginDetailRepository.php`
`app/Models/User.php`
`app/Models/UserProfile.php`
`app/Models/UserModule.php`
`app/Models/Role.php`
`app/Models/Permission.php`
`app/Models/RoleHasPermission.php`
`app/Models/ModelHasRole.php`
`app/Models/ModelHasPermission.php`
`app/Models/LoginDetail.php`
`app/Policies/PermissionsPolicy.php`
`app/Http/Controllers/Auth/AuthController.php`
`app/Http/Controllers/Access/UserAccessController.php`
`app/Http/Controllers/Access/RolesController.php`
`app/Http/Controllers/Access/PermissionController.php`
`app/Http/Controllers/Profile/UserProfileController.php`
`app/Http/Controllers/Logs/LoginLogController.php`
`app/Http/Requests/Auth/*`
`app/Http/Requests/Users/*`
`app/Http/Requests/Roles/*`
`app/Http/Requests/Permissions/*`
`app/Http/Requests/Logs/LoginLogsDataRequest.php`
`app/Data/Auth/RegisterUserData.php`

## Core Shared Identity Models

Move to:

```text
app/Core/Models/
```

Current candidates:

`app/Models/Module.php`
`app/Models/Department.php`

These are platform identity structures, not module business models.

## Core Theme And Platform UI Settings

Move to:

```text
app/Core/Services/UI/
app/Core/Repositories/Contracts/
app/Core/Repositories/Eloquent/
app/Core/Models/
app/Core/Http/Controllers/Settings/
app/Core/Http/Requests/Theme/
```

Current candidates:

`app/Services/UI/ThemeService.php`
`app/Repositories/Contracts/ThemePreferencesRepositoryInterface.php`
`app/Repositories/Eloquent/EloquentThemePreferencesRepository.php`
`app/Models/AppSetting.php`
`app/Models/UserPreference.php`
`app/Http/Controllers/Settings/ThemeController.php`
`app/Http/Requests/Theme/UpdateThemeStyleRequest.php`
`app/Http/Requests/Theme/UpdateThemeColorsRequest.php`

## Core Google Drive And Integrations

Move to:

```text
app/Core/Services/GoogleDrive/
app/Core/Services/Contracts/GoogleDrive/
app/Core/Builders/GoogleDrive/
app/Core/Builders/Contracts/GoogleDrive/
app/Core/Repositories/Contracts/
app/Core/Repositories/Eloquent/
app/Core/Models/
app/Core/Http/Controllers/
app/Core/Http/Requests/Drive/
app/Core/Http/Requests/Files/
```

Current candidates:

`app/Services/GoogleDrive/GoogleDriveConnectionService.php`
`app/Services/GoogleDrive/GoogleDriveClientFactory.php`
`app/Services/GoogleDrive/GoogleDriveFileService.php`
`app/Services/GoogleDrive/GoogleDriveFolderService.php`
`app/Services/GoogleDrive/GoogleDriveSettingsProvider.php`
`app/Services/Contracts/GoogleDrive/*`
`app/Builders/GoogleDrive/GoogleDriveFileMetadataBuilder.php`
`app/Builders/GoogleDrive/GoogleDriveFolderNameSanitizer.php`
`app/Builders/Contracts/GoogleDrive/*`
`app/Repositories/Contracts/GoogleTokenRepositoryInterface.php`
`app/Repositories/Eloquent/EloquentGoogleTokenRepository.php`
`app/Models/GoogleToken.php`
`app/Http/Controllers/GoogleDriveController.php`
`app/Http/Requests/Drive/*`
`app/Http/Requests/Files/StoreDriveFileRequest.php`

## Core Print And Infrastructure

Move to:

```text
app/Core/Services/Infrastructure/
app/Core/Services/Contracts/Infrastructure/
app/Core/Http/Controllers/Reports/
```

Current candidates:

`app/Services/Infrastructure/ChromePdfGenerator.php`
`app/Services/Contracts/Infrastructure/PdfGeneratorInterface.php`
`app/Core/Http/Controllers/Reports/PrintWorkspaceSampleController.php`

`PrintWorkspaceSampleController` is treated as Core because it serves as a reusable print standard sample.

## Core Geocoding

Move to:

```text
app/Core/Services/Geocoding/
app/Core/Services/Contracts/Geocoding/
```

Current candidates:

`app/Services/Geocoding/PositionstackGeocodingService.php`
`app/Services/Contracts/Geocoding/GeocodingServiceInterface.php`

## Core Platform Wiring

Move to:

```text
app/Core/Providers/
app/Core/Http/Middleware/
app/Core/Http/View/
```

Current candidates:

`app/Core/Providers/AppServiceProvider.php`
`app/Core/Providers/AuthServiceProvider.php`
`app/Core/Providers/CoreServiceProvider.php`
`app/Core/Providers/ViewServiceProvider.php`
`app/Http/Middleware/RoleOrPermissionMiddleware.php`
`app/Http/Middleware/EnsurePasswordChanged.php`
`app/Http/Middleware/CheckAdminOrPermission.php`
`app/Http/View/Composers/HeaderComposer.php`

## Core User Administration Builders

Move to:

```text
app/Core/Builders/User/
app/Core/Builders/Contracts/User/
```

Current candidates:

`app/Builders/User/UserDatatableRowBuilder.php`
`app/Builders/User/UserDatatableActionBuilder.php`

These remain Core because user administration is treated as a platform concern.

---

# Shared Capability Target Map

## Tasks Shared Capability

Move to:

```text
app/Core/Builders/Tasks/
  Contracts/
app/Core/Http/Controllers/Tasks/
app/Core/Http/Requests/Tasks/
app/Core/Models/Tasks/
app/Core/Policies/Tasks/
app/Core/Repositories/Tasks/
  Contracts/
  Eloquent/
app/Core/Services/Tasks/
  Contracts/

resources/core/js/tasks/
resources/core/views/tasks/
```

Current candidates:

`app/Core/Services/Tasks/TaskService.php`
`app/Core/Services/Tasks/TaskReadService.php`
`app/Core/Services/Tasks/TaskNotificationService.php`
`app/Core/Services/Tasks/TaskTimelineService.php`
`app/Core/Services/Tasks/TaskShowActionProvider.php`
`app/Core/Services/Tasks/Contracts/*`
`app/Core/Builders/Tasks/TaskAdminStatsBuilder.php`
`app/Core/Builders/Tasks/TaskDatatableRowBuilder.php`
`app/Core/Builders/Tasks/TaskNotificationPayloadBuilder.php`
`app/Core/Builders/Tasks/TaskReassignmentNoteBuilder.php`
`app/Core/Builders/Tasks/TaskTimelineContextMetaBuilder.php`
`app/Core/Builders/Tasks/Contracts/*`
`app/Core/Repositories/Tasks/Contracts/TaskRepositoryInterface.php`
`app/Core/Repositories/Tasks/Contracts/TaskEventRepositoryInterface.php`
`app/Core/Repositories/Tasks/Eloquent/EloquentTaskRepository.php`
`app/Core/Repositories/Tasks/Eloquent/EloquentTaskEventRepository.php`
`app/Core/Models/Tasks/Task.php`
`app/Core/Models/Tasks/TaskEvent.php`
`app/Core/Policies/Tasks/TaskPolicy.php`
`app/Core/Http/Controllers/Tasks/TaskController.php`
`app/Core/Http/Controllers/Tasks/TaskActionController.php`
`app/Core/Http/Requests/Tasks/*`
`resources/core/views/tasks/index.blade.php`
`resources/core/views/tasks/show.blade.php`
`resources/core/js/tasks/*`

`UserTaskReassignOptionBuilder` now lives with the Tasks shared capability under `app/Core/Builders/Tasks/`.

## Core Resource Shell

Move to:

```text
resources/core/
  js/
  views/
```

Current candidates:

`resources/core/views/access/*`
`resources/core/views/audit-logs/*`
`resources/core/views/auth/*`
`resources/core/views/components/*`
`resources/core/views/drive/*`
`resources/core/views/errors/*`
`resources/core/views/layouts/*`
`resources/core/views/logs/*`
`resources/core/views/notifications/*`
`resources/core/views/pages/*`
`resources/core/views/partials/*`
`resources/core/views/print-workspace/*`
`resources/core/views/profile/*`
`resources/core/js/access-permissions/*`
`resources/core/js/access-roles/*`
`resources/core/js/access-users/*`
`resources/core/js/audit-logs/*`
`resources/core/js/auth/*`
`resources/core/js/login-logs/*`
`resources/core/js/notifications/*`
`resources/core/js/app.js`
`resources/core/js/bootstrap.js`
`resources/core/js/custom-entry.js`
`resources/core/js/datatables.js`
`resources/core/js/force-password-change.js`
`resources/core/js/logs-tabulator.js`
`resources/core/js/logs.js`
`resources/core/js/permissions-manage.js`
`resources/core/js/permissions-tabulator.js`
`resources/core/js/permissions.js`
`resources/core/js/roles-page.js`
`resources/core/js/sweetalert.js`
`resources/core/js/theme-switcher.js`

---

# Keep Top Level For Now

The following are framework base pieces and do not need to move immediately:

`app/Http/Controllers/Controller.php`
`app/Http/Requests/BaseFormRequest.php`
`app/Models/Concerns/HasUuid.php`

These can stay top level until a dedicated foundation layer is introduced.

---

# Review Before Moving

The dashboard shell is treated as Core:

`app/Core/Http/Controllers/Dashboard/DashboardsController.php`

Reason:

It currently serves as the platform landing shell, not a module-owned business dashboard.

---

# Migration Rule

Move by concern, not by file randomness.

Recommended order:

1 identify the module concern boundary
2 move PHP code into `app/Modules/<Module>`
3 move views and JS into `resources/modules/<module>`
4 register a module service provider
5 update routes and bindings
6 add focused tests and docs

Keep namespaces, contracts, and providers aligned during each move.

Do not mix platform relocation with unrelated behavior refactors in the same step.
