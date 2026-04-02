<?php

use App\Core\Http\Controllers\WorkflowNotificationSettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password.changed', 'active_module'])->group(function () {
    Route::get('/workflow-notifications', [WorkflowNotificationSettingsController::class, 'index'])
        ->middleware('permission:workflow_notifications.view|workflow_notifications.update')
        ->name('workflow-notifications.index');

    Route::patch('/workflow-notifications', [WorkflowNotificationSettingsController::class, 'update'])
        ->middleware('permission:workflow_notifications.update')
        ->name('workflow-notifications.update');
});
