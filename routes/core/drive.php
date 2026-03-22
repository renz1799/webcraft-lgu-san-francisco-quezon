<?php

use App\Core\Http\Controllers\GoogleDriveController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password.changed', 'active_module'])->group(function () {
    Route::get('/drive', [GoogleDriveController::class, 'index'])->name('drive.index');
    Route::get('/drive/global', [GoogleDriveController::class, 'index']);
    Route::get('/drive/oauth', [GoogleDriveController::class, 'index']);
    Route::post('/drive/connect', [GoogleDriveController::class, 'connect'])->name('drive.connect');
    Route::get('/google/drive/callback', [GoogleDriveController::class, 'callback'])->name('drive.callback');
    Route::post('/drive/disconnect', [GoogleDriveController::class, 'disconnect'])->name('drive.disconnect');
    Route::post('/drive/upload', [GoogleDriveController::class, 'upload'])->name('drive.upload');
    Route::get('/drive/preview/{fileId}', [GoogleDriveController::class, 'preview'])
        ->name('drive.preview');
});
