<?php

use App\Core\Http\Controllers\Notifications\NotificationController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password.changed', 'active_module'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'page'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsReadWeb'])->name('notifications.readAll');
    Route::get('/notifications/header', [NotificationController::class, 'header'])->name('notifications.header');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
});
