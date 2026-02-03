<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserProfileController;

use App\Http\Controllers\UsersAccessController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\PermissionController;

use App\Http\Controllers\LoginLogController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AuditRestoreController;

use App\Http\Controllers\Notifications\NotificationController;
use App\Http\Controllers\Tasks\TaskController;
use App\Http\Controllers\Tasks\TaskActionController;
use App\Http\Controllers\DriveTestController;
use App\Http\Controllers\DriveOAuthController;

/*
|--------------------------------------------------------------------------
| Public / Guest
|--------------------------------------------------------------------------
*/
Route::get('/', [DashboardsController::class, 'index']); // leave public (placeholder)

Route::get('/login',  [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:login')
    ->name('login.attempt');

// Password reset (public)
Route::get('forgot-password',        [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('forgot-password',       [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('reset-password/{token}', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('reset-password',        [\App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');


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
    Route::get('/mail-settings',  [UserProfileController::class, 'index'])->name('profile.index');
    Route::put('/mail-settings',  [UserProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [UserProfileController::class, 'updatePassword'])->name('profile.updatePassword');

    /*
    |--------------------------------------------------------------------------
    | Sign-up / Registration (gated)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role_or_permission:Administrator')->group(function () {
        Route::get('/sign-up',   [AuthController::class, 'showSignUpForm'])->name('sign-up');
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

            Route::get('/permissions/data', [UsersAccessController::class, 'data'])
             ->name('users.permissions.data');

            Route::get('/permissions', [UsersAccessController::class, 'index'])
                ->name('users.permissions.index');

            Route::get('{user}/permissions', [UsersAccessController::class, 'show'])
                ->name('users.permissions.show');

            Route::get('{user}/permissions/edit', [UsersAccessController::class, 'edit'])
                ->name('users.permissions.edit');

            Route::patch('{user}/permissions', [UsersAccessController::class, 'updateModulePermissions'])
                ->name('users.permissions.update');

            Route::patch('{user}/status', [UsersAccessController::class, 'updateStatus'])
                ->name('users.status.update');

            Route::post('{user}/reset-password', [UsersAccessController::class, 'resetPassword'])
                ->name('users.password.reset');

            Route::delete('{user}', [UsersAccessController::class, 'destroy'])
                ->name('users.destroy');

            Route::patch('{user}/restore', [UsersAccessController::class, 'restore'])
                ->name('users.restore');

            Route::delete('{user}/force', [UsersAccessController::class, 'forceDelete'])
                ->name('users.forceDelete');
        });

        /*
        |--------------------------------------------------------------------------
        | Roles CRUD (Administrator ONLY)
        |--------------------------------------------------------------------------
        */
        Route::middleware(['role:Administrator'])->group(function () {
            Route::resource('roles', RolesController::class)->whereUuid(['role']);
        });

        /*
        |--------------------------------------------------------------------------
        | Permissions CRUD (Administrator ONLY)
        |--------------------------------------------------------------------------
        */
        Route::middleware(['role:Administrator'])->group(function () {
            Route::get('/permissions',                 [PermissionController::class, 'index'])->name('permissions.index');
            Route::post('/permissions',                [PermissionController::class, 'store'])->name('permissions.store');
            Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])
                ->whereUuid('permission')
                ->name('permissions.destroy');

            Route::post('/permissions/{permission}/restore', [PermissionController::class, 'restore'])
                ->whereUuid('permission')
                ->name('permissions.restore');

            Route::delete('/permissions/{permission}/force', [PermissionController::class, 'forceDestroy'])
                ->whereUuid('permission')
                ->name('permissions.force');
        });

        /*
        |--------------------------------------------------------------------------
        | Login Logs (Administrator ONLY)
        |--------------------------------------------------------------------------
        */
        Route::middleware(['role:Administrator'])->group(function () {
            Route::get('/login-logs',      [LoginLogController::class, 'index'])->name('logs.index');
            Route::get('/login-logs/data', [LoginLogController::class, 'data'])->name('logs.data');
        });

        /*
        |--------------------------------------------------------------------------
        | Audit Logs + Restore (Administrator ONLY)
        |--------------------------------------------------------------------------
        */
        Route::middleware(['role:Administrator'])->group(function () {
            Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

            // ✅ NEW: Tabulator remote data endpoint
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

        /*
    |--------------------------------------------------------------------------
    | Test Drive Upload
    |--------------------------------------------------------------------------
    */

    Route::get('/drive/test', [DriveTestController::class, 'index'])
        ->name('drive.test.index');

    Route::post('/drive/test', [DriveTestController::class, 'store'])
        ->name('drive.test.store');


    Route::get('/drive/oauth', [DriveOAuthController::class, 'index'])->name('drive.oauth.index');
    Route::post('/drive/oauth/connect', [DriveOAuthController::class, 'connect'])->name('drive.oauth.connect');
    Route::get('/google/drive/callback', [DriveOAuthController::class, 'callback'])->name('drive.oauth.callback');
    Route::post('/drive/oauth/upload', [DriveOAuthController::class, 'upload'])->name('drive.oauth.upload');
    Route::get('/drive/oauth/preview/{fileId}', [DriveOAuthController::class, 'preview'])
    ->name('drive.oauth.preview')
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
