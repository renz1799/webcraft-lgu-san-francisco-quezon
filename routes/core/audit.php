<?php

use App\Core\Http\Controllers\AuditLogs\AuditLogController;
use App\Core\Http\Controllers\AuditLogs\AuditLogPrintController;
use App\Core\Http\Controllers\AuditLogs\AuditRestoreController;
use App\Core\Http\Controllers\Logs\LoginLogController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'password.changed', 'active_module'])->group(function () {
    Route::middleware('role_or_permission:Administrator|admin|view Login Logs')->group(function () {
        Route::get('/login-logs', [LoginLogController::class, 'index'])->name('logs.index');
        Route::get('/login-logs/data', [LoginLogController::class, 'data'])->name('logs.data');
    });

    Route::middleware('role_or_permission:Administrator|admin|view Audit Logs')->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
        Route::get('/audit-logs/data', [AuditLogController::class, 'data'])->name('audit-logs.data');
    });

    Route::middleware('role_or_permission:Administrator|admin|modify Allow Data Restoration')->group(function () {
        Route::post('/audit/restore', [AuditRestoreController::class, 'restore'])
            ->name('audit.restore');
    });

    Route::get('/audit-logs/print', [AuditLogPrintController::class, 'preview'])
        ->name('audit-logs.print.index')
        ->middleware('role_or_permission:Administrator|admin|view Audit Logs');
    Route::get('/audit-logs/print/pdf', [AuditLogPrintController::class, 'downloadPdf'])
        ->name('audit-logs.print.pdf')
        ->middleware('role_or_permission:Administrator|admin|view Audit Logs');
});
