<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardsController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HeaderController;
use App\Http\Controllers\UserProfileController;

use App\Http\Controllers\UsersAccessController;   // user role/perm management (per-user)
use App\Http\Controllers\RolesController;         // roles CRUD
use App\Http\Controllers\PermissionController;    // permissions CRUD

use App\Http\Controllers\LoginLogController;      // login logs (DataTables)
use App\Http\Controllers\AuditLogController;      // audit logs (list)
use App\Http\Controllers\AuditRestoreController;  // audit restore action


/*
|--------------------------------------------------------------------------
| Public / Guest
|--------------------------------------------------------------------------
*/
Route::get('/', [DashboardsController::class, 'index']);

Route::get('/login',  [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

// Password reset (keep these public)
Route::get('forgot-password',        [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('forgot-password',       [\App\Http\Controllers\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('reset-password/{token}', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('reset-password',        [\App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');


/*
|--------------------------------------------------------------------------
| Authenticated
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // logout + small utilities
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/capture-location', [AuthController::class, 'captureLocation']); // if still used
    Route::get('/header', [HeaderController::class, 'renderHeader'])->name('header');

    // Profile / Mail settings
    Route::get('/mail-settings',  [UserProfileController::class, 'index'])->name('profile.index');
    Route::put('/mail-settings',  [UserProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [UserProfileController::class, 'updatePassword'])->name('profile.updatePassword');

    // Sign-up / Registration (page + action) — gated
    Route::middleware('role_or_permission:admin|view User Registration')->group(function () {
        Route::get('/sign-up',   [AuthController::class, 'showSignUpForm'])->name('sign-up');
        Route::post('/register', [AuthController::class, 'register'])->name('register');
    });

    /*
    |--------------------------------------------------------------------------
    | Users: per-user role & permission management
    |--------------------------------------------------------------------------
    */
    Route::prefix('users')->whereUuid(['user'])->group(function () {

        // Page: manage all users' permissions (index list)
        Route::get('/permissions', [UsersAccessController::class, 'index'])
            ->middleware('role_or_permission:admin|view User Lists')
            ->name('users.permissions.index');

        // Single user: view current role/permissions (JSON)
        Route::get('{user}/permissions', [UsersAccessController::class, 'show'])
            ->name('users.permissions.show');

        // Edit page for a single user
        Route::get('{user}/permissions/edit', [UsersAccessController::class, 'edit'])
            ->name('users.permissions.edit');

        // Update role and/or direct permissions (one canonical endpoint)
        Route::patch('{user}/permissions', [UsersAccessController::class, 'updateModulePermissions'])
            ->name('users.permissions.update');

        // Update active status
        Route::patch('{user}/status', [UsersAccessController::class, 'updateStatus'])
            ->name('users.status.update');

        // Reset password (temporary)
        Route::post('{user}/reset-password', [UsersAccessController::class, 'resetPassword'])
            ->name('users.password.reset');

        // Soft delete / restore / force delete
        Route::delete('{user}',            [UsersAccessController::class, 'destroy'])->name('users.destroy');
        Route::patch('{user}/restore',     [UsersAccessController::class, 'restore'])->name('users.restore');
        Route::delete('{user}/force',      [UsersAccessController::class, 'forceDelete'])->name('users.forceDelete');
    });

    /*
    |--------------------------------------------------------------------------
    | Roles CRUD
    |--------------------------------------------------------------------------
    */
    Route::middleware('role_or_permission:admin|view User Lists|modify User Lists|delete User Lists')
        ->group(function () {
            // If your Role IDs are UUIDs, constrain the parameter:
            Route::resource('roles', RolesController::class)
                ->whereUuid(['role']);
        });

    /*
    |--------------------------------------------------------------------------
    | Permissions CRUD
    |--------------------------------------------------------------------------
    */
    Route::middleware('role_or_permission:admin|modify User Permissions')->group(function () {
        Route::get('/permissions',                     [PermissionController::class, 'index'])->name('permissions.index');
        Route::post('/permissions',                    [PermissionController::class, 'store'])->name('permissions.store');
        Route::delete('/permissions/{permission}',     [PermissionController::class, 'destroy'])->whereUuid('permission')->name('permissions.destroy');

        // Soft delete helpers
        Route::post('/permissions/{permission}/restore', [PermissionController::class, 'restore'])->whereUuid('permission')->name('permissions.restore');
        Route::delete('/permissions/{permission}/force',  [PermissionController::class, 'forceDestroy'])->whereUuid('permission')->name('permissions.force');
    });

    /*
    |--------------------------------------------------------------------------
    | Login Logs
    |--------------------------------------------------------------------------
    */
    Route::middleware('role_or_permission:admin|view Login Logs|modify Login Logs|delete Login Logs')
        ->group(function () {
            Route::get('/login-logs',       [LoginLogController::class, 'index'])->name('logs.index');
            Route::get('/login-logs/data',  [LoginLogController::class, 'data'])->name('logs.data');
        });

    /*
    |--------------------------------------------------------------------------
    | Audit Logs + Restore
    |--------------------------------------------------------------------------
    */
    Route::middleware('role_or_permission:admin|view Audit Logs|modify Audit Logs|delete Audit Logs')
        ->group(function () {
            Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        });

    // Generic restore endpoint (protected by request authorize + permission)
    Route::post('/audit/restore', [AuditRestoreController::class, 'restore'])
        ->middleware('role_or_permission:admin|modify Allow Data Restoration')
        ->name('audit.restore');
});

    /*
    |--------------------------------------------------------------------------
    | Theme and Templates
    |--------------------------------------------------------------------------
    */

    Route::middleware('auth')->group(function () {
        Route::post('/theme/style',  [\App\Http\Controllers\ThemeController::class, 'updateStyle'])->name('theme.style.update');
    });
    Route::middleware(['auth','role:admin'])->group(function () {
        Route::post('/theme/colors', [\App\Http\Controllers\ThemeController::class, 'updateColors'])->name('theme.colors.update');
    });