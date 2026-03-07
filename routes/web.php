<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuditRestoreController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardsController;
use App\Http\Controllers\DriveOAuthController;
use App\Http\Controllers\GoogleDrive\DriveGlobalController;
use App\Http\Controllers\LoginLogController;
use App\Http\Controllers\Notifications\NotificationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\Tasks\TaskActionController;
use App\Http\Controllers\Tasks\TaskController;
use App\Http\Controllers\UserAccessController;
use App\Http\Controllers\UserProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public / Guest
|--------------------------------------------------------------------------
*/
Route::get('/', [DashboardsController::class, 'index']); // leave public (placeholder)

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:login')
    ->name('login.attempt');


/*
|--------------------------------------------------------------------------
| Authenticated + Enforced Password Change
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'password.changed'])->group(function () {

    // logout + small utilities
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/capture-location', [AuthController::class, 'captureLocation']); // if still used

    // Profile / Mail settings
    Route::get('/mail-settings', [UserProfileController::class, 'index'])->name('profile.index');
    Route::put('/mail-settings', [UserProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [UserProfileController::class, 'updatePassword'])->name('profile.updatePassword');

    /*
    |--------------------------------------------------------------------------
    | Sign-up / Registration (gated)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role_or_permission:Administrator')->group(function () {
        Route::get('/sign-up', [AuthController::class, 'showSignUpForm'])->name('sign-up');
        Route::post('/register', [AuthController::class, 'register'])->name('register');
    });

    /*
    |--------------------------------------------------------------------------
    | Users: Administrator only (your latest decision)
    |--------------------------------------------------------------------------
    */
    Route::prefix('users')
        ->whereUuid(['user'])
        ->middleware('role:Administrator')
        ->group(function () {

            // canonical access.* routes
            Route::get('/permissions/data', [UserAccessController::class, 'data'])->name('access.users.data');
            Route::get('/permissions', [UserAccessController::class, 'index'])->name('access.users.index');
            Route::get('{user}/permissions', [UserAccessController::class, 'show'])->name('access.users.show');
            Route::get('{user}/permissions/edit', [UserAccessController::class, 'edit'])->name('access.users.edit');
            Route::patch('{user}/permissions', [UserAccessController::class, 'updateModulePermissions'])->name('access.users.update');
            Route::patch('{user}/status', [UserAccessController::class, 'updateStatus'])->name('access.users.status.update');
            Route::post('{user}/reset-password', [UserAccessController::class, 'resetPassword'])->name('access.users.password.reset');
            Route::delete('{user}', [UserAccessController::class, 'destroy'])->name('access.users.destroy');
            Route::patch('{user}/restore', [UserAccessController::class, 'restore'])->name('access.users.restore');


        });

    /*
    |--------------------------------------------------------------------------
    | Roles CRUD (Administrator ONLY)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:Administrator'])->group(function () {
        Route::resource('roles', RolesController::class)
            ->whereUuid(['role'])
            ->except(['show']);

        // canonical aliases
        Route::get('/roles/data', [RolesController::class, 'data'])->name('access.roles.data');
        Route::get('/roles', [RolesController::class, 'index'])->name('access.roles.index');
        Route::get('/roles/create', [RolesController::class, 'create'])->name('access.roles.create');
        Route::post('/roles', [RolesController::class, 'store'])->name('access.roles.store');
        Route::get('/roles/{role}/edit', [RolesController::class, 'edit'])->whereUuid('role')->name('access.roles.edit');
        Route::match(['put', 'patch'], '/roles/{role}', [RolesController::class, 'update'])->whereUuid('role')->name('access.roles.update');
        Route::delete('/roles/{role}', [RolesController::class, 'destroy'])->whereUuid('role')->name('access.roles.destroy');

        Route::patch('/roles/{role}/restore', [RolesController::class, 'restore'])->whereUuid('role')->name('access.roles.restore');
    });

    /*
    |--------------------------------------------------------------------------
    | Permissions CRUD (Administrator ONLY)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:Administrator'])->group(function () {
        // canonical access.* routes
        Route::get('/permissions', [PermissionController::class, 'index'])->name('access.permissions.index');
        Route::post('/permissions', [PermissionController::class, 'store'])->name('access.permissions.store');
        Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])
            ->whereUuid('permission')
            ->name('access.permissions.destroy');

        Route::post('/permissions/{permission}/restore', [PermissionController::class, 'restore'])
            ->whereUuid('permission')
            ->name('access.permissions.restore');

        Route::delete('/permissions/{permission}/force', [PermissionController::class, 'forceDestroy'])
            ->whereUuid('permission')
            ->name('access.permissions.force');
    });

    /*
    |--------------------------------------------------------------------------
    | Login Logs (Administrator ONLY)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:Administrator'])->group(function () {
        Route::get('/login-logs', [LoginLogController::class, 'index'])->name('logs.index');
        Route::get('/login-logs/data', [LoginLogController::class, 'data'])->name('logs.data');
    });

    /*
    |--------------------------------------------------------------------------
    | Audit Logs + Restore (Administrator ONLY)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:Administrator'])->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

        Route::get('/audit-logs/data', [AuditLogController::class, 'data'])->name('audit-logs.data');

        Route::post('/audit/restore', [AuditRestoreController::class, 'restore'])
            ->name('audit.restore');
    });

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */
    Route::get('/notifications', [NotificationController::class, 'page'])
        ->name('notifications.index');

    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsReadWeb'])
        ->name('notifications.readAll');

    Route::get('/notifications/header', [NotificationController::class, 'header'])->name('notifications.header');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');

    /*
    |--------------------------------------------------------------------------
    | Tasks (uuid constrained)
    |--------------------------------------------------------------------------
    */
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');

    Route::get('/tasks/data', [TaskController::class, 'data'])
        ->name('tasks.data');

    Route::get('/tasks/{id}', [TaskController::class, 'show'])
        ->whereUuid('id')
        ->name('tasks.show');

    Route::post('/tasks', [TaskActionController::class, 'store'])
        ->name('tasks.store');

    Route::post('/tasks/{id}/status', [TaskActionController::class, 'changeStatus'])
        ->whereUuid('id')
        ->name('tasks.status.update');

    Route::post('/tasks/{id}/comment', [TaskActionController::class, 'comment'])
        ->whereUuid('id')
        ->name('tasks.comment.store');

    Route::post('/tasks/{id}/reassign', [TaskActionController::class, 'reassign'])
        ->whereUuid('id')
        ->name('tasks.reassign');

    Route::post('/tasks/{id}/claim', [TaskActionController::class, 'claim'])
        ->whereUuid('id')
        ->name('tasks.claim');

    Route::get('/drive/oauth', [DriveOAuthController::class, 'index'])->name('drive.oauth.index');
    Route::post('/drive/oauth/connect', [DriveOAuthController::class, 'connect'])->name('drive.oauth.connect');
    Route::get('/google/drive/callback', [DriveOAuthController::class, 'callback'])->name('drive.oauth.callback');
    Route::post('/drive/oauth/upload', [DriveOAuthController::class, 'upload'])->name('drive.oauth.upload');
    Route::get('/drive/oauth/preview/{fileId}', [DriveOAuthController::class, 'preview'])
        ->name('drive.oauth.preview')
        ->middleware(['auth']);

    Route::get('/drive/global', [DriveGlobalController::class, 'index'])->name('drive.global.index');
    Route::post('/drive/global/connect', [DriveGlobalController::class, 'connect'])->name('drive.global.connect');
    Route::get('/google/drive/callback', [DriveGlobalController::class, 'callback'])->name('drive.global.callback');
    Route::post('/drive/global/disconnect', [DriveGlobalController::class, 'disconnect'])->name('drive.global.disconnect');
    Route::post('/drive/global/upload', [DriveGlobalController::class, 'upload'])->name('drive.global.upload');
    Route::get('/drive/global/preview/{fileId}', [DriveGlobalController::class, 'preview'])
        ->name('drive.global.preview')
        ->middleware(['auth']);

    /*
    |--------------------------------------------------------------------------
    | Theme
    |--------------------------------------------------------------------------
    */
    Route::post('/theme/style', [\App\Http\Controllers\ThemeController::class, 'updateStyle'])
        ->name('theme.style.update');

    Route::post('/theme/colors', [\App\Http\Controllers\ThemeController::class, 'updateColors'])
        ->middleware('role:Administrator')
        ->name('theme.colors.update');
});

