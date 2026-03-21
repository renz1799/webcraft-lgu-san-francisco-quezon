<?php

use App\Core\Http\Controllers\Access\PermissionController;
use App\Core\Http\Controllers\Access\RolesController;
use App\Core\Http\Controllers\Access\UserAccessController;
use App\Core\Http\Controllers\AuditLogs\AuditLogController;
use App\Core\Http\Controllers\AuditLogs\AuditLogPrintController;
use App\Core\Http\Controllers\AuditLogs\AuditRestoreController;
use App\Core\Http\Controllers\Auth\AuthController;
use App\Core\Http\Controllers\Dashboard\DashboardsController;
use App\Core\Http\Controllers\GoogleDriveController;
use App\Core\Http\Controllers\Logs\LoginLogController;
use App\Core\Http\Controllers\Notifications\NotificationController;
use App\Core\Http\Controllers\Profile\UserProfileController;
use App\Core\Http\Controllers\Reports\PrintWorkspaceSampleController;
use App\Core\Http\Controllers\Settings\ThemeController;
use App\Modules\Tasks\Http\Controllers\TaskActionController;
use App\Modules\Tasks\Http\Controllers\TaskController;
use Illuminate\Http\Request;
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

    // Profile
    Route::get('/profile', [UserProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [UserProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [UserProfileController::class, 'updatePassword'])->name('profile.updatePassword');
    Route::get('/mail-settings', function (Request $request) {
        return redirect()->route('profile.index', $request->query());
    });
    Route::put('/mail-settings', [UserProfileController::class, 'update']);

    /*
    |--------------------------------------------------------------------------
    | Sign-up / Registration (gated)
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:Administrator')->group(function () {
        Route::get('/sign-up', [AuthController::class, 'showSignUpForm'])->name('sign-up');
        Route::get('/register/options', [AuthController::class, 'registrationOptions'])->name('register.options');
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
        Route::get('/permissions/data', [PermissionController::class, 'data'])->name('access.permissions.data');
        Route::get('/permissions', [PermissionController::class, 'index'])->name('access.permissions.index');
        Route::post('/permissions', [PermissionController::class, 'store'])->name('access.permissions.store');
        Route::patch('/permissions/{permission}', [PermissionController::class, 'update'])
            ->whereUuid('permission')
            ->name('access.permissions.update');
        Route::delete('/permissions/{permission}', [PermissionController::class, 'destroy'])
            ->whereUuid('permission')
            ->name('access.permissions.destroy');

        Route::patch('/permissions/{permission}/restore', [PermissionController::class, 'restore'])
            ->whereUuid('permission')
            ->name('access.permissions.restore');
    });

    /*
    |--------------------------------------------------------------------------
    | Login Logs (Administrator ONLY)
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role_or_permission:Administrator|admin|view Login Logs'])->group(function () {
        Route::get('/login-logs', [LoginLogController::class, 'index'])->name('logs.index');
        Route::get('/login-logs/data', [LoginLogController::class, 'data'])->name('logs.data');
    });

    /*
    |--------------------------------------------------------------------------
    | Audit Logs + Restore
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role_or_permission:Administrator|admin|view Audit Logs'])->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');

        Route::get('/audit-logs/data', [AuditLogController::class, 'data'])->name('audit-logs.data');
    });

    Route::middleware(['role_or_permission:Administrator|admin|modify Allow Data Restoration'])->group(function () {
        Route::post('/audit/restore', [AuditRestoreController::class, 'restore'])
            ->name('audit.restore');
    });

    Route::get('/reports/samples/rpcppe-preview', [PrintWorkspaceSampleController::class, 'rpcppe'])
        ->name('reports.samples.rpcppe');
    Route::get('/reports/samples/rpcppe-preview/pdf', [PrintWorkspaceSampleController::class, 'rpcppePdf'])
        ->name('reports.samples.rpcppe.pdf');
        
    //PRINTING 
    Route::get('/audit-logs/print', [AuditLogPrintController::class, 'preview'])
        ->name('audit-logs.print.index')
        ->middleware(['role_or_permission:Administrator|admin|view Audit Logs']);

    Route::get('/audit-logs/print/pdf', [AuditLogPrintController::class, 'downloadPdf'])
        ->name('audit-logs.print.pdf')
        ->middleware(['role_or_permission:Administrator|admin|view Audit Logs']);

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

    Route::delete('/tasks/{id}', [TaskActionController::class, 'destroy'])
        ->whereUuid('id')
        ->name('tasks.destroy');

    Route::patch('/tasks/{id}/restore', [TaskActionController::class, 'restore'])
        ->whereUuid('id')
        ->name('tasks.restore');

    Route::get('/drive', [GoogleDriveController::class, 'index'])->name('drive.index');
    Route::get('/drive/global', [GoogleDriveController::class, 'index']);
    Route::get('/drive/oauth', [GoogleDriveController::class, 'index']);
    Route::post('/drive/connect', [GoogleDriveController::class, 'connect'])->name('drive.connect');
    Route::get('/google/drive/callback', [GoogleDriveController::class, 'callback'])->name('drive.callback');
    Route::post('/drive/disconnect', [GoogleDriveController::class, 'disconnect'])->name('drive.disconnect');
    Route::post('/drive/upload', [GoogleDriveController::class, 'upload'])->name('drive.upload');
    Route::get('/drive/preview/{fileId}', [GoogleDriveController::class, 'preview'])
        ->name('drive.preview')
        ->middleware(['auth']);

    /*
    |--------------------------------------------------------------------------
    | Theme
    |--------------------------------------------------------------------------
    */
    Route::post('/theme/style', [ThemeController::class, 'updateStyle'])
        ->name('theme.style.update');

    Route::post('/theme/colors', [ThemeController::class, 'updateColors'])
        ->middleware('role_or_permission:Administrator|admin')
        ->name('theme.colors.update');
});










