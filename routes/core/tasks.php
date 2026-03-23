<?php

use App\Core\Http\Controllers\Tasks\TaskActionController;
use App\Core\Http\Controllers\Tasks\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password.changed', 'active_module'])
    ->prefix('tasks')
    ->as('tasks.')
    ->group(function () {
        Route::get('/', [TaskController::class, 'index'])->name('index');
        Route::get('/my', fn () => redirect()->route('tasks.index', ['scope' => 'mine', 'archived' => 'active']))
            ->name('my');
        Route::get('/available', fn () => redirect()->route('tasks.index', ['scope' => 'available', 'archived' => 'active']))
            ->name('available');
        Route::get('/data', [TaskController::class, 'data'])->name('data');
        Route::get('/{id}', [TaskController::class, 'show'])
            ->whereUuid('id')
            ->name('show');
        Route::post('/', [TaskActionController::class, 'store'])->name('store');
        Route::post('/{id}/status', [TaskActionController::class, 'changeStatus'])
            ->whereUuid('id')
            ->name('status.update');
        Route::post('/{id}/comment', [TaskActionController::class, 'comment'])
            ->whereUuid('id')
            ->name('comment.store');
        Route::post('/{id}/reassign', [TaskActionController::class, 'reassign'])
            ->whereUuid('id')
            ->name('reassign');
        Route::post('/{id}/claim', [TaskActionController::class, 'claim'])
            ->whereUuid('id')
            ->name('claim');
        Route::delete('/{id}', [TaskActionController::class, 'destroy'])
            ->whereUuid('id')
            ->name('destroy');
        Route::patch('/{id}/restore', [TaskActionController::class, 'restore'])
            ->whereUuid('id')
            ->name('restore');
    });
