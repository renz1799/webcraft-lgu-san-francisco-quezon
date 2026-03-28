<?php

use App\Core\Http\Controllers\AccountablePersons\AccountablePersonActionController as CoreAccountablePersonActionController;
use App\Core\Http\Controllers\AccountablePersons\AccountablePersonController as CoreAccountablePersonController;
use App\Modules\GSO\Http\Controllers\AccountableOfficers\AccountableOfficerActionController;
use App\Modules\GSO\Http\Controllers\Air\AirActionController;
use App\Modules\GSO\Http\Controllers\Air\AirController;
use App\Modules\GSO\Http\Controllers\Air\AirFileController;
use App\Modules\GSO\Http\Controllers\Air\AirInventoryPromotionController;
use App\Modules\GSO\Http\Controllers\Air\AirInspectionController;
use App\Modules\GSO\Http\Controllers\Air\AirInspectionUnitController;
use App\Modules\GSO\Http\Controllers\Air\AirInspectionUnitFileController;
use App\Modules\GSO\Http\Controllers\Air\AirItemController;
use App\Modules\GSO\Http\Controllers\Air\AirPrintController;
use App\Modules\GSO\Http\Controllers\Air\AirRisController;
use App\Modules\GSO\Http\Controllers\AssetCategories\AssetCategoryActionController;
use App\Modules\GSO\Http\Controllers\AssetCategories\AssetCategoryController;
use App\Modules\GSO\Http\Controllers\AssetTypes\AssetTypeActionController;
use App\Modules\GSO\Http\Controllers\AssetTypes\AssetTypeController;
use App\Modules\GSO\Http\Controllers\Departments\DepartmentActionController;
use App\Modules\GSO\Http\Controllers\Departments\DepartmentController;
use App\Modules\GSO\Http\Controllers\FundClusters\FundClusterActionController;
use App\Modules\GSO\Http\Controllers\FundClusters\FundClusterController;
use App\Modules\GSO\Http\Controllers\FundSources\FundSourceActionController;
use App\Modules\GSO\Http\Controllers\FundSources\FundSourceController;
use App\Modules\GSO\Http\Controllers\GsoDashboardController;
use App\Modules\GSO\Http\Controllers\GsoWorkspaceController;
use App\Modules\GSO\Http\Controllers\ICS\IcsController;
use App\Modules\GSO\Http\Controllers\ICS\IcsItemController;
use App\Modules\GSO\Http\Controllers\ICS\IcsPrintController;
use App\Modules\GSO\Http\Controllers\ICS\IcsWorkflowController;
use App\Modules\GSO\Http\Controllers\Inspections\InspectionActionController;
use App\Modules\GSO\Http\Controllers\Inspections\InspectionController;
use App\Modules\GSO\Http\Controllers\Inspections\InspectionPhotoController;
use App\Modules\GSO\Http\Controllers\InventoryItems\InventoryItemActionController;
use App\Modules\GSO\Http\Controllers\InventoryItems\InventoryItemController;
use App\Modules\GSO\Http\Controllers\InventoryItems\InventoryItemEventController;
use App\Modules\GSO\Http\Controllers\InventoryItems\InventoryItemFileController;
use App\Modules\GSO\Http\Controllers\InventoryItems\InventoryItemPropertyCardController;
use App\Modules\GSO\Http\Controllers\InventoryItems\PublicInventoryAssetController;
use App\Modules\GSO\Http\Controllers\Items\ItemActionController;
use App\Modules\GSO\Http\Controllers\Items\ItemController;
use App\Modules\GSO\Http\Controllers\ITR\ItrController;
use App\Modules\GSO\Http\Controllers\ITR\ItrItemController;
use App\Modules\GSO\Http\Controllers\ITR\ItrPrintController;
use App\Modules\GSO\Http\Controllers\ITR\ItrWorkflowController;
use App\Modules\GSO\Http\Controllers\PAR\ParController;
use App\Modules\GSO\Http\Controllers\PAR\ParItemController;
use App\Modules\GSO\Http\Controllers\PAR\ParPrintController;
use App\Modules\GSO\Http\Controllers\PAR\ParWorkflowController;
use App\Modules\GSO\Http\Controllers\PTR\PtrController;
use App\Modules\GSO\Http\Controllers\PTR\PtrItemController;
use App\Modules\GSO\Http\Controllers\PTR\PtrPrintController;
use App\Modules\GSO\Http\Controllers\PTR\PtrWorkflowController;
use App\Modules\GSO\Http\Controllers\Reports\RegspiReportController;
use App\Modules\GSO\Http\Controllers\Reports\PropertyCardsReportController;
use App\Modules\GSO\Http\Controllers\Reports\RspiReportController;
use App\Modules\GSO\Http\Controllers\Reports\RrspReportController;
use App\Modules\GSO\Http\Controllers\Reports\RpcppeReportController;
use App\Modules\GSO\Http\Controllers\Reports\RpcspReportController;
use App\Modules\GSO\Http\Controllers\RIS\RisController;
use App\Modules\GSO\Http\Controllers\RIS\RisItemController;
use App\Modules\GSO\Http\Controllers\RIS\RisPrintController;
use App\Modules\GSO\Http\Controllers\RIS\RisWorkflowController;
use App\Modules\GSO\Http\Controllers\Stocks\StockController;
use App\Modules\GSO\Http\Controllers\WMR\WmrController;
use App\Modules\GSO\Http\Controllers\WMR\WmrItemController;
use App\Modules\GSO\Http\Controllers\WMR\WmrPrintController;
use App\Modules\GSO\Http\Controllers\WMR\WmrWorkflowController;
use App\Modules\GSO\Models\Ics;
use App\Modules\GSO\Models\Itr;
use App\Modules\GSO\Models\Par;
use App\Modules\GSO\Models\Ptr;
use App\Modules\GSO\Models\Ris;
use App\Modules\GSO\Models\Wmr;
use App\Core\Http\Controllers\Access\ModuleUserOnboardingController;
use App\Core\Http\Controllers\Access\PermissionController;
use App\Core\Http\Controllers\Access\RolesController;
use App\Core\Http\Controllers\Access\UserAccessController;
use App\Core\Http\Controllers\AuditLogs\AuditLogController;
use App\Core\Http\Controllers\AuditLogs\AuditLogPrintController;
use App\Core\Http\Controllers\AuditLogs\AuditRestoreController;
use Illuminate\Support\Facades\Route;

Route::bind('ics', fn ($value) => $value instanceof Ics ? $value : Ics::withTrashed()->findOrFail($value));
Route::bind('ris', fn ($value) => $value instanceof Ris ? $value : Ris::withTrashed()->findOrFail($value));
Route::bind('par', fn ($value) => $value instanceof Par ? $value : Par::withTrashed()->findOrFail($value));
Route::bind('ptr', fn ($value) => $value instanceof Ptr ? $value : Ptr::withTrashed()->findOrFail($value));
Route::bind('itr', fn ($value) => $value instanceof Itr ? $value : Itr::withTrashed()->findOrFail($value));
Route::bind('wmr', fn ($value) => $value instanceof Wmr ? $value : Wmr::withTrashed()->findOrFail($value));

Route::get('/gso/assets/{code}', [PublicInventoryAssetController::class, 'show'])
    ->name('gso.public-assets.show');
Route::get('/gso/assets/{code}/files/{file}/preview', [PublicInventoryAssetController::class, 'preview'])
    ->whereUuid('file')
    ->name('gso.public-assets.files.preview');
Route::get('/asset/{code}', function (string $code) {
    return redirect()->route('gso.public-assets.show', ['code' => $code]);
})->name('gso.public-assets.legacy.show');
Route::get('/asset/{code}/files/{file}/preview', function (string $code, string $file) {
    return redirect()->route('gso.public-assets.files.preview', [
        'code' => $code,
        'file' => $file,
    ]);
})->whereUuid('file')->name('gso.public-assets.legacy.files.preview');

Route::middleware(['auth', 'password.changed'])->group(function () {
    Route::prefix('gso')
        ->as('gso.')
        ->middleware('module:gso')
        ->group(function () {
            Route::get('/', GsoDashboardController::class)->name('dashboard');

            Route::middleware('role:Administrator|admin')->group(function () {
                Route::get('/users/data', [UserAccessController::class, 'data'])->name('access.users.data');
                Route::get('/users', [UserAccessController::class, 'index'])->name('access.users.index');
                Route::get('/users/create', [ModuleUserOnboardingController::class, 'create'])->name('access.users.create');
                Route::post('/users', [ModuleUserOnboardingController::class, 'store'])->name('access.users.store');

                Route::get('/users/permissions/data', [UserAccessController::class, 'data'])->name('legacy.access.users.data');
                Route::get('/users/permissions', [UserAccessController::class, 'index'])->name('legacy.access.users.index');

                Route::prefix('users')
                    ->whereUuid(['user'])
                    ->group(function () {
                        Route::get('{user}/permissions', [UserAccessController::class, 'show'])->name('access.users.show');
                        Route::get('{user}/permissions/edit', [UserAccessController::class, 'edit'])->name('access.users.edit');
                        Route::patch('{user}/permissions', [UserAccessController::class, 'updateModulePermissions'])->name('access.users.update');
                        Route::patch('{user}/toggle-status', [UserAccessController::class, 'updateStatus'])->name('access.users.status.update');
                    });

                Route::get('/roles/data', [RolesController::class, 'data'])->name('access.roles.data');
                Route::get('/roles', [RolesController::class, 'index'])->name('access.roles.index');
                Route::get('/roles/create', [RolesController::class, 'create'])->name('access.roles.create');
                Route::post('/roles', [RolesController::class, 'store'])->name('access.roles.store');
                Route::get('/roles/{role}/edit', [RolesController::class, 'edit'])->whereUuid('role')->name('access.roles.edit');
                Route::match(['put', 'patch'], '/roles/{role}', [RolesController::class, 'update'])->whereUuid('role')->name('access.roles.update');
                Route::delete('/roles/{role}', [RolesController::class, 'destroy'])->whereUuid('role')->name('access.roles.destroy');
                Route::patch('/roles/{role}/restore', [RolesController::class, 'restore'])->whereUuid('role')->name('access.roles.restore');

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

            Route::middleware('role_or_permission:Administrator|admin|view Audit Logs')->group(function () {
                Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
                Route::get('/audit-logs/data', [AuditLogController::class, 'data'])->name('audit-logs.data');
                Route::get('/audit-logs/print', [AuditLogPrintController::class, 'preview'])->name('audit-logs.print.index');
                Route::get('/audit-logs/print/pdf', [AuditLogPrintController::class, 'downloadPdf'])->name('audit-logs.print.pdf');
            });

            Route::middleware('role_or_permission:Administrator|admin|modify Allow Data Restoration')->group(function () {
                Route::post('/audit/restore', [AuditRestoreController::class, 'restore'])->name('audit.restore');
            });

            Route::get('/ris', [RisController::class, 'index'])->name('ris.index');
            Route::get('/ris/data', [RisController::class, 'data'])->name('ris.data');
            Route::post('/ris/create-draft', [RisController::class, 'createDraft'])->name('ris.create-draft');
            Route::get('/ris/{ris}/edit', [RisController::class, 'edit'])->whereUuid('ris')->name('ris.edit');
            Route::put('/ris/{ris}', [RisController::class, 'update'])->whereUuid('ris')->name('ris.update');
            Route::delete('/ris/{ris}', [RisController::class, 'destroy'])->whereUuid('ris')->name('ris.destroy');
            Route::patch('/ris/{ris}/restore', [RisController::class, 'restore'])->whereUuid('ris')->name('ris.restore');
            Route::get('/ris/{ris}/print', [RisPrintController::class, 'print'])->whereUuid('ris')->name('ris.print');
            Route::get('/ris/{ris}/print/pdf', [RisPrintController::class, 'downloadPdf'])->whereUuid('ris')->name('ris.print.pdf');
            Route::get('/ris/{ris}/items', [RisItemController::class, 'list'])->whereUuid('ris')->name('ris.items.list');
            Route::get('/ris/{ris}/items/suggest', [RisItemController::class, 'suggest'])->whereUuid('ris')->name('ris.items.suggest');
            Route::post('/ris/{ris}/items/add', [RisItemController::class, 'add'])->whereUuid('ris')->name('ris.items.add');
            Route::put('/ris/{ris}/items', [RisItemController::class, 'bulkUpdate'])->whereUuid('ris')->name('ris.items.bulk-update');
            Route::put('/ris/{ris}/items/{risItem}', [RisItemController::class, 'update'])->whereUuid(['ris', 'risItem'])->name('ris.items.update');
            Route::delete('/ris/{ris}/items/{risItem}', [RisItemController::class, 'remove'])->whereUuid(['ris', 'risItem'])->name('ris.items.remove');
            Route::post('/ris/{ris}/submit', [RisWorkflowController::class, 'submit'])->whereUuid('ris')->name('ris.submit');
            Route::post('/ris/{ris}/approve', [RisWorkflowController::class, 'approve'])->whereUuid('ris')->name('ris.approve');
            Route::post('/ris/{ris}/reject', [RisWorkflowController::class, 'reject'])->whereUuid('ris')->name('ris.reject');
            Route::post('/ris/{ris}/reopen', [RisWorkflowController::class, 'reopen'])->whereUuid('ris')->name('ris.reopen');
            Route::post('/ris/{ris}/revert-to-draft', [RisWorkflowController::class, 'revertToDraft'])->whereUuid('ris')->name('ris.revert-to-draft');
            Route::get('/pars', [ParController::class, 'index'])->name('pars.index');
            Route::get('/pars/data', [ParController::class, 'data'])->name('pars.data');
            Route::post('/pars/create-draft', [ParController::class, 'createDraft'])->name('pars.create-draft');
            Route::get('/pars/create', [ParController::class, 'create'])->name('pars.create');
            Route::post('/pars', [ParController::class, 'store'])->name('pars.store');
            Route::get('/pars/{par}', [ParController::class, 'show'])->whereUuid('par')->name('pars.show');
            Route::put('/pars/{par}', [ParController::class, 'update'])->whereUuid('par')->name('pars.update');
            Route::delete('/pars/{par}', [ParController::class, 'destroy'])->whereUuid('par')->name('pars.destroy');
            Route::patch('/pars/{par}/restore', [ParController::class, 'restore'])->whereUuid('par')->name('pars.restore');
            Route::post('/pars/{par}/submit', [ParWorkflowController::class, 'submit'])->whereUuid('par')->name('pars.submit');
            Route::post('/pars/{par}/reopen', [ParWorkflowController::class, 'reopen'])->whereUuid('par')->name('pars.reopen');
            Route::post('/pars/{par}/finalize', [ParWorkflowController::class, 'finalize'])->whereUuid('par')->name('pars.finalize');
            Route::post('/pars/{par}/cancel', [ParWorkflowController::class, 'cancel'])->whereUuid('par')->name('pars.cancel');
            Route::get('/pars/{par}/items/suggest', [ParItemController::class, 'suggest'])->whereUuid('par')->name('pars.items.suggest');
            Route::post('/pars/{par}/items', [ParItemController::class, 'store'])->whereUuid('par')->name('pars.items.store');
            Route::delete('/pars/{par}/items/{parItem}', [ParItemController::class, 'destroy'])->whereUuid(['par', 'parItem'])->name('pars.items.destroy');
            Route::get('/pars/{par}/print', [ParPrintController::class, 'print'])->whereUuid('par')->name('pars.print');
            Route::get('/pars/{par}/print/pdf', [ParPrintController::class, 'downloadPdf'])->whereUuid('par')->name('pars.print.pdf');
            Route::get('/ics', [IcsController::class, 'index'])->name('ics.index');
            Route::get('/ics/data', [IcsController::class, 'data'])->name('ics.data');
            Route::post('/ics/create-draft', [IcsController::class, 'createDraft'])->name('ics.create-draft');
            Route::get('/ics/{ics}/edit', [IcsController::class, 'edit'])->whereUuid('ics')->name('ics.edit');
            Route::put('/ics/{ics}', [IcsController::class, 'update'])->whereUuid('ics')->name('ics.update');
            Route::delete('/ics/{ics}', [IcsController::class, 'destroy'])->whereUuid('ics')->name('ics.destroy');
            Route::patch('/ics/{ics}/restore', [IcsController::class, 'restore'])->whereUuid('ics')->name('ics.restore');
            Route::post('/ics/{ics}/submit', [IcsWorkflowController::class, 'submit'])->whereUuid('ics')->name('ics.submit');
            Route::post('/ics/{ics}/reopen', [IcsWorkflowController::class, 'reopen'])->whereUuid('ics')->name('ics.reopen');
            Route::post('/ics/{ics}/finalize', [IcsWorkflowController::class, 'finalize'])->whereUuid('ics')->name('ics.finalize');
            Route::post('/ics/{ics}/cancel', [IcsWorkflowController::class, 'cancel'])->whereUuid('ics')->name('ics.cancel');
            Route::get('/ics/{ics}/items', [IcsItemController::class, 'list'])->whereUuid('ics')->name('ics.items.list');
            Route::get('/ics/{ics}/items/suggest', [IcsItemController::class, 'suggest'])->whereUuid('ics')->name('ics.items.suggest');
            Route::post('/ics/{ics}/items', [IcsItemController::class, 'store'])->whereUuid('ics')->name('ics.items.store');
            Route::delete('/ics/{ics}/items/{icsItem}', [IcsItemController::class, 'destroy'])->whereUuid(['ics', 'icsItem'])->name('ics.items.destroy');
            Route::get('/ics/{ics}/print', [IcsPrintController::class, 'print'])->whereUuid('ics')->name('ics.print');
            Route::get('/ics/{ics}/print/pdf', [IcsPrintController::class, 'downloadPdf'])->whereUuid('ics')->name('ics.print.pdf');
            Route::get('/ptrs', [PtrController::class, 'index'])->name('ptrs.index');
            Route::get('/ptrs/data', [PtrController::class, 'data'])->name('ptrs.data');
            Route::post('/ptrs/create-draft', [PtrController::class, 'createDraft'])->name('ptrs.create-draft');
            Route::get('/ptrs/{ptr}/edit', [PtrController::class, 'edit'])->whereUuid('ptr')->name('ptrs.edit');
            Route::put('/ptrs/{ptr}', [PtrController::class, 'update'])->whereUuid('ptr')->name('ptrs.update');
            Route::delete('/ptrs/{ptr}', [PtrController::class, 'destroy'])->whereUuid('ptr')->name('ptrs.destroy');
            Route::patch('/ptrs/{ptr}/restore', [PtrController::class, 'restore'])->whereUuid('ptr')->name('ptrs.restore');
            Route::post('/ptrs/{ptr}/submit', [PtrWorkflowController::class, 'submit'])->whereUuid('ptr')->name('ptrs.submit');
            Route::post('/ptrs/{ptr}/reopen', [PtrWorkflowController::class, 'reopen'])->whereUuid('ptr')->name('ptrs.reopen');
            Route::post('/ptrs/{ptr}/finalize', [PtrWorkflowController::class, 'finalize'])->whereUuid('ptr')->name('ptrs.finalize');
            Route::post('/ptrs/{ptr}/cancel', [PtrWorkflowController::class, 'cancel'])->whereUuid('ptr')->name('ptrs.cancel');
            Route::get('/ptrs/{ptr}/items/list', [PtrItemController::class, 'list'])->whereUuid('ptr')->name('ptrs.items.list');
            Route::get('/ptrs/{ptr}/items/suggest', [PtrItemController::class, 'suggest'])->whereUuid('ptr')->name('ptrs.items.suggest');
            Route::post('/ptrs/{ptr}/items', [PtrItemController::class, 'store'])->whereUuid('ptr')->name('ptrs.items.store');
            Route::delete('/ptrs/{ptr}/items/{ptrItem}', [PtrItemController::class, 'destroy'])->whereUuid(['ptr', 'ptrItem'])->name('ptrs.items.destroy');
            Route::get('/ptrs/{ptr}/print', [PtrPrintController::class, 'print'])->whereUuid('ptr')->name('ptrs.print');
            Route::get('/ptrs/{ptr}/print/pdf', [PtrPrintController::class, 'downloadPdf'])->whereUuid('ptr')->name('ptrs.print.pdf');
            Route::get('/itrs', [ItrController::class, 'index'])->name('itrs.index');
            Route::get('/itrs/data', [ItrController::class, 'data'])->name('itrs.data');
            Route::post('/itrs/create-draft', [ItrController::class, 'createDraft'])->name('itrs.create-draft');
            Route::get('/itrs/{itr}/edit', [ItrController::class, 'edit'])->whereUuid('itr')->name('itrs.edit');
            Route::put('/itrs/{itr}', [ItrController::class, 'update'])->whereUuid('itr')->name('itrs.update');
            Route::delete('/itrs/{itr}', [ItrController::class, 'destroy'])->whereUuid('itr')->name('itrs.destroy');
            Route::patch('/itrs/{itr}/restore', [ItrController::class, 'restore'])->whereUuid('itr')->name('itrs.restore');
            Route::post('/itrs/{itr}/submit', [ItrWorkflowController::class, 'submit'])->whereUuid('itr')->name('itrs.submit');
            Route::post('/itrs/{itr}/reopen', [ItrWorkflowController::class, 'reopen'])->whereUuid('itr')->name('itrs.reopen');
            Route::post('/itrs/{itr}/finalize', [ItrWorkflowController::class, 'finalize'])->whereUuid('itr')->name('itrs.finalize');
            Route::post('/itrs/{itr}/cancel', [ItrWorkflowController::class, 'cancel'])->whereUuid('itr')->name('itrs.cancel');
            Route::get('/itrs/{itr}/items', [ItrItemController::class, 'list'])->whereUuid('itr')->name('itrs.items.list');
            Route::get('/itrs/{itr}/items/suggest', [ItrItemController::class, 'suggest'])->whereUuid('itr')->name('itrs.items.suggest');
            Route::post('/itrs/{itr}/items', [ItrItemController::class, 'store'])->whereUuid('itr')->name('itrs.items.store');
            Route::delete('/itrs/{itr}/items/{itrItem}', [ItrItemController::class, 'destroy'])->whereUuid(['itr', 'itrItem'])->name('itrs.items.destroy');
            Route::get('/itrs/{itr}/print', [ItrPrintController::class, 'print'])->whereUuid('itr')->name('itrs.print');
            Route::get('/itrs/{itr}/print/pdf', [ItrPrintController::class, 'downloadPdf'])->whereUuid('itr')->name('itrs.print.pdf');
            Route::get('/wmrs', [WmrController::class, 'index'])->name('wmrs.index');
            Route::get('/wmrs/data', [WmrController::class, 'data'])->name('wmrs.data');
            Route::post('/wmrs/create-draft', [WmrController::class, 'createDraft'])->name('wmrs.createDraft');
            Route::get('/wmrs/{wmr}/edit', [WmrController::class, 'edit'])->whereUuid('wmr')->name('wmrs.edit');
            Route::get('/wmrs/{wmr}/print', [WmrPrintController::class, 'print'])->whereUuid('wmr')->name('wmrs.print');
            Route::get('/wmrs/{wmr}/print/pdf', [WmrPrintController::class, 'downloadPdf'])->whereUuid('wmr')->name('wmrs.print.pdf');
            Route::get('/wmrs/{wmr}/items/suggest', [WmrItemController::class, 'suggest'])->whereUuid('wmr')->name('wmrs.items.suggest');
            Route::get('/wmrs/{wmr}/items/list', [WmrItemController::class, 'list'])->whereUuid('wmr')->name('wmrs.items.list');
            Route::post('/wmrs/{wmr}/items', [WmrItemController::class, 'store'])->whereUuid('wmr')->name('wmrs.items.store');
            Route::patch('/wmrs/{wmr}/items/{wmrItem}', [WmrItemController::class, 'update'])->whereUuid(['wmr', 'wmrItem'])->name('wmrs.items.update');
            Route::delete('/wmrs/{wmr}/items/{wmrItem}', [WmrItemController::class, 'destroy'])->whereUuid(['wmr', 'wmrItem'])->name('wmrs.items.destroy');
            Route::put('/wmrs/{wmr}', [WmrController::class, 'update'])->whereUuid('wmr')->name('wmrs.update');
            Route::post('/wmrs/{wmr}/submit', [WmrWorkflowController::class, 'submit'])->whereUuid('wmr')->name('wmrs.submit');
            Route::post('/wmrs/{wmr}/approve', [WmrWorkflowController::class, 'approve'])->whereUuid('wmr')->name('wmrs.approve');
            Route::post('/wmrs/{wmr}/reopen', [WmrWorkflowController::class, 'reopen'])->whereUuid('wmr')->name('wmrs.reopen');
            Route::post('/wmrs/{wmr}/finalize', [WmrWorkflowController::class, 'finalize'])->whereUuid('wmr')->name('wmrs.finalize');
            Route::post('/wmrs/{wmr}/cancel', [WmrWorkflowController::class, 'cancel'])->whereUuid('wmr')->name('wmrs.cancel');
            Route::delete('/wmrs/{wmr}', [WmrController::class, 'destroy'])->whereUuid('wmr')->name('wmrs.destroy');
            Route::patch('/wmrs/{wmr}/restore', [WmrController::class, 'restore'])->whereUuid('wmr')->name('wmrs.restore');

            Route::get('/air', [AirController::class, 'index'])->name('air.index');
            Route::get('/air/data', [AirController::class, 'data'])->name('air.data');
            Route::get('/air/create', [AirController::class, 'create'])->name('air.create');
            Route::get('/air/{air}/edit', [AirController::class, 'edit'])->whereUuid('air')->name('air.edit');
            Route::put('/air/{air}', [AirActionController::class, 'update'])->whereUuid('air')->name('air.update');
            Route::put('/air/{air}/submit', [AirActionController::class, 'submit'])->whereUuid('air')->name('air.submit');
            Route::post('/air/{air}/follow-up', [AirActionController::class, 'createFollowUp'])->whereUuid('air')->name('air.follow-up.create');
            Route::delete('/air/{air}', [AirActionController::class, 'destroy'])->whereUuid('air')->name('air.destroy');
            Route::patch('/air/{air}/restore', [AirActionController::class, 'restore'])->whereUuid('air')->name('air.restore');
            Route::delete('/air/{air}/force', [AirActionController::class, 'forceDestroy'])->whereUuid('air')->name('air.force-destroy');
            Route::get('/air/{air}/items', [AirItemController::class, 'index'])->whereUuid('air')->name('air.items.index');
            Route::get('/air/{air}/items/suggest', [AirItemController::class, 'suggest'])->whereUuid('air')->name('air.items.suggest');
            Route::post('/air/{air}/items', [AirItemController::class, 'store'])->whereUuid('air')->name('air.items.store');
            Route::put('/air/{air}/items', [AirItemController::class, 'bulkUpdate'])->whereUuid('air')->name('air.items.bulk-update');
            Route::put('/air/{air}/items/{airItem}', [AirItemController::class, 'update'])->whereUuid(['air', 'airItem'])->name('air.items.update');
            Route::delete('/air/{air}/items/{airItem}', [AirItemController::class, 'destroy'])->whereUuid(['air', 'airItem'])->name('air.items.destroy');
            Route::get('/air/{air}/inspect', [AirInspectionController::class, 'show'])->whereUuid('air')->name('air.inspect');
            Route::get('/air/{air}/print', [AirPrintController::class, 'preview'])->whereUuid('air')->name('air.print');
            Route::get('/air/{air}/print/pdf', [AirPrintController::class, 'downloadPdf'])->whereUuid('air')->name('air.print.pdf');
            Route::get('/air/{air}/files', [AirFileController::class, 'index'])->whereUuid('air')->name('air.files.index');
            Route::post('/air/{air}/files', [AirFileController::class, 'store'])->whereUuid('air')->name('air.files.store');
            Route::get('/air/{air}/files/{file}/preview', [AirFileController::class, 'preview'])->whereUuid(['air', 'file'])->name('air.files.preview');
            Route::delete('/air/{air}/files/{file}', [AirFileController::class, 'destroy'])->whereUuid(['air', 'file'])->name('air.files.destroy');
            Route::put('/air/{air}/files/{file}/primary', [AirFileController::class, 'setPrimary'])->whereUuid(['air', 'file'])->name('air.files.set-primary');
            Route::put('/air/{air}/inspection', [AirInspectionController::class, 'save'])->whereUuid('air')->name('air.inspection.save');
            Route::put('/air/{air}/inspection/finalize', [AirInspectionController::class, 'finalize'])->whereUuid('air')->name('air.inspection.finalize');
            Route::post('/air/{air}/inspection/reopen', [AirInspectionController::class, 'reopen'])->whereUuid('air')->name('air.inspection.reopen');
            Route::post('/air/{air}/ris/generate', [AirRisController::class, 'generate'])->whereUuid('air')->name('air.ris.generate');
            Route::get('/air/{air}/inventory/eligible', [AirInventoryPromotionController::class, 'eligible'])
                ->whereUuid('air')
                ->name('air.inventory.eligible');
            Route::post('/air/{air}/inventory/promote', [AirInventoryPromotionController::class, 'promote'])
                ->whereUuid('air')
                ->name('air.inventory.promote');
            Route::get('/air/{air}/inspection/items/{airItem}/units', [AirInspectionUnitController::class, 'index'])
                ->whereUuid(['air', 'airItem'])
                ->name('air.inspection.units.index');
            Route::put('/air/{air}/inspection/items/{airItem}/units', [AirInspectionUnitController::class, 'save'])
                ->whereUuid(['air', 'airItem'])
                ->name('air.inspection.units.save');
            Route::delete('/air/{air}/inspection/items/{airItem}/units/{unit}', [AirInspectionUnitController::class, 'destroy'])
                ->whereUuid(['air', 'airItem', 'unit'])
                ->name('air.inspection.units.destroy');
            Route::get('/air/{air}/inspection/items/{airItem}/units/{unit}/files', [AirInspectionUnitFileController::class, 'index'])
                ->whereUuid(['air', 'airItem', 'unit'])
                ->name('air.inspection.unit-files.index');
            Route::post('/air/{air}/inspection/items/{airItem}/units/{unit}/files', [AirInspectionUnitFileController::class, 'store'])
                ->whereUuid(['air', 'airItem', 'unit'])
                ->name('air.inspection.unit-files.store');
            Route::get('/air/{air}/inspection/items/{airItem}/units/{unit}/files/{file}/preview', [AirInspectionUnitFileController::class, 'preview'])
                ->whereUuid(['air', 'airItem', 'unit', 'file'])
                ->name('air.inspection.unit-files.preview');
            Route::delete('/air/{air}/inspection/items/{airItem}/units/{unit}/files/{file}', [AirInspectionUnitFileController::class, 'destroy'])
                ->whereUuid(['air', 'airItem', 'unit', 'file'])
                ->name('air.inspection.unit-files.destroy');
            Route::put('/air/{air}/inspection/items/{airItem}/units/{unit}/files/{file}/primary', [AirInspectionUnitFileController::class, 'setPrimary'])
                ->whereUuid(['air', 'airItem', 'unit', 'file'])
                ->name('air.inspection.unit-files.set-primary');
            Route::prefix('inventory/items')
                ->as('items.')
                ->group(function () {
                    Route::get('/', [ItemController::class, 'index'])->name('index');
                    Route::get('/data', [ItemController::class, 'data'])->name('data');
                    Route::get('/{item}', [ItemActionController::class, 'show'])->whereUuid('item')->name('show');
                    Route::post('/', [ItemActionController::class, 'store'])->name('store');
                    Route::put('/{item}', [ItemActionController::class, 'update'])->whereUuid('item')->name('update');
                    Route::delete('/{item}', [ItemActionController::class, 'destroy'])->whereUuid('item')->name('destroy');
                    Route::patch('/{item}/restore', [ItemActionController::class, 'restore'])->whereUuid('item')->name('restore');
                });
            Route::get('/items', fn () => redirect()->route('gso.items.index', request()->query()))
                ->name('legacy.items.index');
            Route::get('/items/data', [ItemController::class, 'data']);
            Route::get('/items/{item}', [ItemActionController::class, 'show'])->whereUuid('item');
            Route::post('/items', [ItemActionController::class, 'store']);
            Route::put('/items/{item}', [ItemActionController::class, 'update'])->whereUuid('item');
            Route::delete('/items/{item}', [ItemActionController::class, 'destroy'])->whereUuid('item');
            Route::patch('/items/{item}/restore', [ItemActionController::class, 'restore'])->whereUuid('item');
            Route::prefix('inventory/inventory-items')
                ->as('inventory-items.')
                ->group(function () {
                    Route::get('/', [InventoryItemController::class, 'index'])->name('index');
                    Route::get('/data', [InventoryItemController::class, 'data'])->name('data');
                    Route::get('/{inventoryItem}', [InventoryItemActionController::class, 'show'])->whereUuid('inventoryItem')->name('show');
                    Route::post('/', [InventoryItemActionController::class, 'store'])->name('store');
                    Route::put('/{inventoryItem}', [InventoryItemActionController::class, 'update'])->whereUuid('inventoryItem')->name('update');
                    Route::delete('/{inventoryItem}', [InventoryItemActionController::class, 'destroy'])->whereUuid('inventoryItem')->name('destroy');
                    Route::patch('/{inventoryItem}/restore', [InventoryItemActionController::class, 'restore'])->whereUuid('inventoryItem')->name('restore');
                    Route::get('/{inventoryItem}/files', [InventoryItemFileController::class, 'index'])->whereUuid('inventoryItem')->name('files.index');
                    Route::post('/{inventoryItem}/files', [InventoryItemFileController::class, 'store'])->whereUuid('inventoryItem')->name('files.store');
                    Route::post('/{inventoryItem}/files/import-inspection', [InventoryItemFileController::class, 'importInspection'])->whereUuid('inventoryItem')->name('files.import-inspection');
                    Route::get('/{inventoryItem}/files/{file}/preview', [InventoryItemFileController::class, 'preview'])
                        ->whereUuid(['inventoryItem', 'file'])
                        ->name('files.preview');
                    Route::delete('/{inventoryItem}/files/{file}', [InventoryItemFileController::class, 'destroy'])
                        ->whereUuid(['inventoryItem', 'file'])
                        ->name('files.destroy');
                    Route::get('/{inventoryItem}/events', [InventoryItemEventController::class, 'index'])->whereUuid('inventoryItem')->name('events.index');
                    Route::post('/{inventoryItem}/events', [InventoryItemEventController::class, 'store'])->whereUuid('inventoryItem')->name('events.store');
                    Route::get('/{inventoryItem}/property-card/print', [InventoryItemPropertyCardController::class, 'print'])
                        ->whereUuid('inventoryItem')
                        ->name('property-card.print');
                    Route::get('/property-cards/print', function () {
                        return redirect()->route('gso.reports.property-cards.print', request()->query());
                    })
                        ->name('property-cards.print-batch');
                });
            Route::get('/inventory-items', fn () => redirect()->route('gso.inventory-items.index', request()->query()))
                ->name('legacy.inventory-items.index');
            Route::get('/inventory-items/data', [InventoryItemController::class, 'data']);
            Route::get('/inventory-items/{inventoryItem}', [InventoryItemActionController::class, 'show'])->whereUuid('inventoryItem');
            Route::post('/inventory-items', [InventoryItemActionController::class, 'store']);
            Route::put('/inventory-items/{inventoryItem}', [InventoryItemActionController::class, 'update'])->whereUuid('inventoryItem');
            Route::delete('/inventory-items/{inventoryItem}', [InventoryItemActionController::class, 'destroy'])->whereUuid('inventoryItem');
            Route::patch('/inventory-items/{inventoryItem}/restore', [InventoryItemActionController::class, 'restore'])->whereUuid('inventoryItem');
            Route::get('/inventory-items/{inventoryItem}/files', [InventoryItemFileController::class, 'index'])->whereUuid('inventoryItem');
            Route::post('/inventory-items/{inventoryItem}/files', [InventoryItemFileController::class, 'store'])->whereUuid('inventoryItem');
            Route::post('/inventory-items/{inventoryItem}/files/import-inspection', [InventoryItemFileController::class, 'importInspection'])->whereUuid('inventoryItem');
            Route::get('/inventory-items/{inventoryItem}/files/{file}/preview', [InventoryItemFileController::class, 'preview'])
                ->whereUuid(['inventoryItem', 'file']);
            Route::delete('/inventory-items/{inventoryItem}/files/{file}', [InventoryItemFileController::class, 'destroy'])
                ->whereUuid(['inventoryItem', 'file']);
            Route::get('/inventory-items/{inventoryItem}/events', [InventoryItemEventController::class, 'index'])->whereUuid('inventoryItem');
            Route::post('/inventory-items/{inventoryItem}/events', [InventoryItemEventController::class, 'store'])->whereUuid('inventoryItem');
            Route::get('/inventory-items/{inventoryItem}/property-card/print', [InventoryItemPropertyCardController::class, 'print'])
                ->whereUuid('inventoryItem');
            Route::get('/inventory-items/property-cards/print', function () {
                return redirect()->route('gso.reports.property-cards.print', request()->query());
            });
            Route::get('/reports/property-cards/print', [PropertyCardsReportController::class, 'print'])
                ->name('reports.property-cards.print');
            Route::get('/reports/property-cards/print/pdf', [PropertyCardsReportController::class, 'downloadPdf'])
                ->name('reports.property-cards.print.pdf');
            Route::get('/reports/regspi/print', [RegspiReportController::class, 'print'])
                ->name('reports.regspi.print');
            Route::get('/reports/regspi/print/pdf', [RegspiReportController::class, 'downloadPdf'])
                ->name('reports.regspi.print.pdf');
            Route::get('/inventory-items/reports/regspi/print', function () {
                return redirect()->route('gso.reports.regspi.print', request()->query());
            })->name('inventory-items.regspi.print');
            Route::get('/reports/rspi/print', [RspiReportController::class, 'print'])
                ->name('reports.rspi.print');
            Route::get('/reports/rspi/print/pdf', [RspiReportController::class, 'downloadPdf'])
                ->name('reports.rspi.print.pdf');
            Route::get('/inventory-items/reports/rspi/print', function () {
                return redirect()->route('gso.reports.rspi.print', request()->query());
            })->name('inventory-items.rspi.print');
            Route::get('/reports/rrsp/print', [RrspReportController::class, 'print'])
                ->name('reports.rrsp.print');
            Route::get('/reports/rrsp/print/pdf', [RrspReportController::class, 'downloadPdf'])
                ->name('reports.rrsp.print.pdf');
            Route::get('/inventory-items/reports/rrsp/print', function () {
                return redirect()->route('gso.reports.rrsp.print', request()->query());
            })->name('inventory-items.rrsp.print');
            Route::get('/reports/rpci/print', [StockController::class, 'printRpci'])
                ->name('stocks.rpci.print');
            Route::get('/reports/rpci/print/pdf', [StockController::class, 'downloadRpciPdf'])
                ->name('stocks.rpci.print.pdf');
            Route::get('/reports/ssmi/print', [StockController::class, 'printSsmi'])
                ->name('stocks.ssmi.print');
            Route::get('/reports/ssmi/print/pdf', [StockController::class, 'downloadSsmiPdf'])
                ->name('stocks.ssmi.print.pdf');
            Route::get('/reports/rpcppe/print', [RpcppeReportController::class, 'print'])
                ->name('reports.rpcppe.print');
            Route::get('/reports/rpcppe/print/pdf', [RpcppeReportController::class, 'downloadPdf'])
                ->name('reports.rpcppe.print.pdf');
            Route::get('/inventory-items/reports/rpcppe/print', function () {
                return redirect()->route('gso.reports.rpcppe.print', request()->query());
            })->name('inventory-items.rpcppe.print');
            Route::get('/reports/rpcsp/print', [RpcspReportController::class, 'print'])
                ->name('reports.rpcsp.print');
            Route::get('/reports/rpcsp/print/pdf', [RpcspReportController::class, 'downloadPdf'])
                ->name('reports.rpcsp.print.pdf');
            Route::get('/inventory-items/reports/rpcsp/print', function () {
                return redirect()->route('gso.reports.rpcsp.print', request()->query());
            })->name('inventory-items.rpcsp.print');
            Route::get('/reports', fn () => app(GsoWorkspaceController::class)->show('reports'))
                ->name('reports.index');
            Route::get('/reports/{page}', function (string $page) {
                if ($page === 'property-cards') {
                    return redirect()->route('gso.reports.property-cards.print', ['preview' => 1] + request()->query());
                }

                if ($page === 'stock-card') {
                    return redirect()->route('gso.stocks.index', ['view' => 'stock-cards'] + request()->query());
                }

                return app(GsoWorkspaceController::class)->show('reports-'.$page);
            })->whereIn('page', ['rpci', 'rpcppe', 'rpcsp', 'regspi', 'rspi', 'rrsp', 'ssmi', 'property-cards', 'stock-card'])
                ->name('reports.show');
            Route::get('/inventory', fn () => app(GsoWorkspaceController::class)->show('inventory'))
                ->name('inventory.index');
            Route::get('/inventory/{page}', function (string $page) {
                return match ($page) {
                    'stocks-ledger' => app(GsoWorkspaceController::class)->show('stocks'),
                };
            })->whereIn('page', ['stocks-ledger'])
                ->name('inventory.show');
            Route::get('/inspections', [InspectionController::class, 'index'])->name('inspections.index');
            Route::get('/inspections/data', [InspectionController::class, 'data'])->name('inspections.data');
            Route::get('/inspections/{inspection}', [InspectionActionController::class, 'show'])->whereUuid('inspection')->name('inspections.show');
            Route::post('/inspections', [InspectionActionController::class, 'store'])->name('inspections.store');
            Route::put('/inspections/{inspection}', [InspectionActionController::class, 'update'])->whereUuid('inspection')->name('inspections.update');
            Route::delete('/inspections/{inspection}', [InspectionActionController::class, 'destroy'])->whereUuid('inspection')->name('inspections.destroy');
            Route::patch('/inspections/{inspection}/restore', [InspectionActionController::class, 'restore'])->whereUuid('inspection')->name('inspections.restore');
            Route::get('/inspections/{inspection}/photos', [InspectionPhotoController::class, 'index'])->whereUuid('inspection')->name('inspections.photos.index');
            Route::post('/inspections/{inspection}/photos', [InspectionPhotoController::class, 'store'])->whereUuid('inspection')->name('inspections.photos.store');
            Route::delete('/inspections/{inspection}/photos/{photo}', [InspectionPhotoController::class, 'destroy'])
                ->whereUuid(['inspection', 'photo'])
                ->name('inspections.photos.destroy');
            Route::get('/stocks', [StockController::class, 'index'])->name('stocks.index');
            Route::get('/stocks/data', [StockController::class, 'data'])->name('stocks.data');
            Route::get('/stocks/{item}/ledger', [StockController::class, 'ledger'])
                ->whereUuid('item')
                ->name('stocks.ledger');
            Route::get('/stocks/{item}/card/print', [StockController::class, 'printCard'])
                ->whereUuid('item')
                ->name('stocks.card.print');
            Route::post('/stocks/adjust', [StockController::class, 'adjust'])->name('stocks.adjust');
            Route::get('/asset-types', [AssetTypeController::class, 'index'])->name('asset-types.index');
            Route::get('/asset-types/data', [AssetTypeController::class, 'data'])->name('asset-types.data');
            Route::post('/asset-types', [AssetTypeActionController::class, 'store'])->name('asset-types.store');
            Route::put('/asset-types/{assetType}', [AssetTypeActionController::class, 'update'])->whereUuid('assetType')->name('asset-types.update');
            Route::delete('/asset-types/{assetType}', [AssetTypeActionController::class, 'destroy'])->whereUuid('assetType')->name('asset-types.destroy');
            Route::patch('/asset-types/{assetType}/restore', [AssetTypeActionController::class, 'restore'])->whereUuid('assetType')->name('asset-types.restore');
            Route::get('/asset-categories', [AssetCategoryController::class, 'index'])->name('asset-categories.index');
            Route::get('/asset-categories/data', [AssetCategoryController::class, 'data'])->name('asset-categories.data');
            Route::post('/asset-categories', [AssetCategoryActionController::class, 'store'])->name('asset-categories.store');
            Route::put('/asset-categories/{assetCategory}', [AssetCategoryActionController::class, 'update'])->whereUuid('assetCategory')->name('asset-categories.update');
            Route::delete('/asset-categories/{assetCategory}', [AssetCategoryActionController::class, 'destroy'])->whereUuid('assetCategory')->name('asset-categories.destroy');
            Route::patch('/asset-categories/{assetCategory}/restore', [AssetCategoryActionController::class, 'restore'])->whereUuid('assetCategory')->name('asset-categories.restore');
            Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
            Route::get('/departments/data', [DepartmentController::class, 'data'])->name('departments.data');
            Route::post('/departments', [DepartmentActionController::class, 'store'])->name('departments.store');
            Route::put('/departments/{department}', [DepartmentActionController::class, 'update'])->whereUuid('department')->name('departments.update');
            Route::delete('/departments/{department}', [DepartmentActionController::class, 'destroy'])->whereUuid('department')->name('departments.destroy');
            Route::patch('/departments/{department}/restore', [DepartmentActionController::class, 'restore'])->whereUuid('department')->name('departments.restore');
            Route::get('/fund-clusters', [FundClusterController::class, 'index'])->name('fund-clusters.index');
            Route::get('/fund-clusters/data', [FundClusterController::class, 'data'])->name('fund-clusters.data');
            Route::post('/fund-clusters', [FundClusterActionController::class, 'store'])->name('fund-clusters.store');
            Route::put('/fund-clusters/{fundCluster}', [FundClusterActionController::class, 'update'])->whereUuid('fundCluster')->name('fund-clusters.update');
            Route::delete('/fund-clusters/{fundCluster}', [FundClusterActionController::class, 'destroy'])->whereUuid('fundCluster')->name('fund-clusters.destroy');
            Route::patch('/fund-clusters/{fundCluster}/restore', [FundClusterActionController::class, 'restore'])->whereUuid('fundCluster')->name('fund-clusters.restore');
            Route::get('/fund-sources', [FundSourceController::class, 'index'])->name('fund-sources.index');
            Route::get('/fund-sources/data', [FundSourceController::class, 'data'])->name('fund-sources.data');
            Route::post('/fund-sources', [FundSourceActionController::class, 'store'])->name('fund-sources.store');
            Route::put('/fund-sources/{fundSource}', [FundSourceActionController::class, 'update'])->whereUuid('fundSource')->name('fund-sources.update');
            Route::delete('/fund-sources/{fundSource}', [FundSourceActionController::class, 'destroy'])->whereUuid('fundSource')->name('fund-sources.destroy');
            Route::patch('/fund-sources/{fundSource}/restore', [FundSourceActionController::class, 'restore'])->whereUuid('fundSource')->name('fund-sources.restore');
            Route::get('/accountable-persons', [CoreAccountablePersonController::class, 'index'])->name('accountable-persons.index');
            Route::get('/accountable-persons/data', [CoreAccountablePersonController::class, 'data'])->name('accountable-persons.data');
            Route::get('/accountable-persons/suggest', [CoreAccountablePersonController::class, 'suggest'])->name('accountable-persons.suggest');
            Route::post('/accountable-persons/resolve', [AccountableOfficerActionController::class, 'resolve'])->name('accountable-persons.resolve');
            Route::post('/accountable-persons', [CoreAccountablePersonActionController::class, 'store'])->name('accountable-persons.store');
            Route::put('/accountable-persons/{accountablePerson}', [CoreAccountablePersonActionController::class, 'update'])->whereUuid('accountablePerson')->name('accountable-persons.update');
            Route::delete('/accountable-persons/{accountablePerson}', [CoreAccountablePersonActionController::class, 'destroy'])->whereUuid('accountablePerson')->name('accountable-persons.destroy');
            Route::patch('/accountable-persons/{accountablePerson}/restore', [CoreAccountablePersonActionController::class, 'restore'])->whereUuid('accountablePerson')->name('accountable-persons.restore');
            Route::redirect('/accountable-officers', '/gso/accountable-persons');
        });

    Route::get('/air', function () {
        return redirect()->route('gso.air.index');
    });
    Route::get('/ris', function () {
        return redirect()->route('gso.ris.index');
    });
    Route::get('/pars', function () {
        return redirect()->route('gso.pars.index');
    });
    Route::get('/pars/{par}', function (string $par) {
        return redirect()->route('gso.pars.show', ['par' => $par] + request()->query());
    })->whereUuid('par');
    Route::get('/pars/{par}/print', function (string $par) {
        return redirect()->route('gso.pars.print', ['par' => $par] + request()->query());
    })->whereUuid('par');
    Route::get('/ics', function () {
        return redirect()->route('gso.ics.index');
    });
    Route::get('/ics/{ics}/edit', function (string $ics) {
        return redirect()->route('gso.ics.edit', ['ics' => $ics] + request()->query());
    })->whereUuid('ics');
    Route::get('/ics/{ics}/print', function (string $ics) {
        return redirect()->route('gso.ics.print', ['ics' => $ics] + request()->query());
    })->whereUuid('ics');
    Route::get('/ics/{ics}/print/pdf', function (string $ics) {
        return redirect()->route('gso.ics.print.pdf', ['ics' => $ics] + request()->query());
    })->whereUuid('ics')->name('gso.ics.legacy.print.pdf');
    Route::get('/wmrs', function () {
        return redirect()->route('gso.wmrs.index');
    });
    Route::get('/wmrs/{wmr}/edit', function (string $wmr) {
        return redirect()->route('gso.wmrs.edit', ['wmr' => $wmr] + request()->query());
    })->whereUuid('wmr');
    Route::get('/wmrs/{wmr}/print', function (string $wmr) {
        return redirect()->route('gso.wmrs.print', ['wmr' => $wmr] + request()->query());
    })->whereUuid('wmr');
    Route::get('/wmrs/{wmr}/print/pdf', function (string $wmr) {
        return redirect()->route('gso.wmrs.print.pdf', ['wmr' => $wmr] + request()->query());
    })->whereUuid('wmr')->name('gso.wmrs.legacy.print.pdf');
    Route::get('/ris/{ris}/edit', function (string $ris) {
        return redirect()->route('gso.ris.edit', ['ris' => $ris] + request()->query());
    })->whereUuid('ris');
    Route::get('/ris/{ris}/print', function (string $ris) {
        return redirect()->route('gso.ris.print', ['ris' => $ris] + request()->query());
    })->whereUuid('ris');
    Route::get('/ris/{ris}/print/pdf', function (string $ris) {
        return redirect()->route('gso.ris.print.pdf', ['ris' => $ris] + request()->query());
    })->whereUuid('ris')->name('gso.ris.legacy.print.pdf');
    Route::get('/air/{air}/edit', function (string $air) {
        return redirect()->route('gso.air.edit', ['air' => $air] + request()->query());
    })->whereUuid('air');
    Route::get('/air/{air}/inspect', function (string $air) {
        return redirect()->route('gso.air.inspect', ['air' => $air] + request()->query());
    })->whereUuid('air');
    Route::get('/air/{air}/print', function (string $air) {
        return redirect()->route('gso.air.print', ['air' => $air] + request()->query());
    })->whereUuid('air')->name('gso.air.legacy.print');
    Route::get('/air/{air}/print/pdf', function (string $air) {
        return redirect()->route('gso.air.print.pdf', ['air' => $air] + request()->query());
    })->whereUuid('air')->name('gso.air.legacy.print.pdf');
    Route::redirect('/items', '/gso/items');
    Route::redirect('/inventory-items', '/gso/inventory/inventory-items');
    Route::redirect('/reports', '/gso/reports');
    Route::get('/inventory-items/{inventoryItem}/property-card/print', function (string $inventoryItem) {
        return redirect()->route('gso.inventory-items.property-card.print', [
            'inventoryItem' => $inventoryItem,
            'preview' => request()->query('preview'),
        ]);
    })->whereUuid('inventoryItem');
    Route::get('/inventory-items/property-cards/print', function () {
        return redirect()->route('gso.reports.property-cards.print', request()->query());
    });
    Route::get('/reports/property-cards/print', function () {
        return redirect()->route('gso.reports.property-cards.print', request()->query());
    });
    Route::get('/reports/regspi/print', function () {
        return redirect()->route('gso.reports.regspi.print', request()->query());
    });
    Route::get('/reports/rspi/print', function () {
        return redirect()->route('gso.reports.rspi.print', request()->query());
    });
    Route::get('/reports/rrsp/print', function () {
        return redirect()->route('gso.reports.rrsp.print', request()->query());
    });
    Route::get('/reports/rpci/print', function () {
        return redirect()->route('gso.stocks.rpci.print', request()->query());
    });
    Route::get('/reports/ssmi/print', function () {
        return redirect()->route('gso.stocks.ssmi.print', request()->query());
    });
    Route::get('/reports/stock-card', function () {
        return redirect()->route('gso.stocks.index', ['view' => 'stock-cards'] + request()->query());
    });
    Route::get('/reports/rpcppe/print', function () {
        return redirect()->route('gso.reports.rpcppe.print', request()->query());
    });
    Route::get('/reports/rpcsp/print', function () {
        return redirect()->route('gso.reports.rpcsp.print', request()->query());
    });
    Route::redirect('/inspections', '/gso/inspections');
    Route::get('/stocks/{item}/ledger', function (string $item) {
        return redirect()->route('gso.stocks.ledger', ['item' => $item] + request()->query());
    })->whereUuid('item');
    Route::get('/stocks/{item}/card/print', function (string $item) {
        return redirect()->route('gso.stocks.card.print', ['item' => $item] + request()->query());
    })->whereUuid('item');
    Route::redirect('/stocks', '/gso/stocks');
    Route::redirect('/asset-types', '/gso/asset-types');
    Route::redirect('/asset-categories', '/gso/asset-categories');
    Route::redirect('/departments', '/gso/departments');
    Route::redirect('/fund-sources', '/gso/fund-sources');
    Route::redirect('/fund-clusters', '/gso/fund-clusters');
    Route::redirect('/accountable-persons', '/gso/accountable-persons');
    Route::redirect('/accountable-officers', '/gso/accountable-persons');

    Route::middleware('module:gso')->group(function () {
        Route::get('/air/data', [AirController::class, 'data']);
        Route::get('/air/create', [AirController::class, 'create']);
        Route::get('/air/{air}/files', [AirFileController::class, 'index'])->whereUuid('air');
        Route::post('/air/{air}/files', [AirFileController::class, 'store'])->whereUuid('air');
        Route::get('/air/{air}/files/{file}/preview', [AirFileController::class, 'preview'])->whereUuid(['air', 'file']);
        Route::delete('/air/{air}/files/{file}', [AirFileController::class, 'destroy'])->whereUuid(['air', 'file']);
        Route::put('/air/{air}/files/{file}/primary', [AirFileController::class, 'setPrimary'])->whereUuid(['air', 'file']);
        Route::put('/air/{air}', [AirActionController::class, 'update'])->whereUuid('air');
        Route::put('/air/{air}/submit', [AirActionController::class, 'submit'])->whereUuid('air');
        Route::post('/air/{air}/follow-up', [AirActionController::class, 'createFollowUp'])->whereUuid('air');
        Route::delete('/air/{air}', [AirActionController::class, 'destroy'])->whereUuid('air');
        Route::patch('/air/{air}/restore', [AirActionController::class, 'restore'])->whereUuid('air');
        Route::get('/air/{air}/items', [AirItemController::class, 'index'])->whereUuid('air');
        Route::get('/air/{air}/items/suggest', [AirItemController::class, 'suggest'])->whereUuid('air');
        Route::get('/air/{air}/item-suggestions', [AirItemController::class, 'suggest'])->whereUuid('air');
        Route::post('/air/{air}/items', [AirItemController::class, 'store'])->whereUuid('air');
        Route::put('/air/{air}/items', [AirItemController::class, 'bulkUpdate'])->whereUuid('air');
        Route::put('/air/{air}/items/{airItem}', [AirItemController::class, 'update'])->whereUuid(['air', 'airItem']);
        Route::delete('/air/{air}/items/{airItem}', [AirItemController::class, 'destroy'])->whereUuid(['air', 'airItem']);
        Route::delete('/air/{air}/force', [AirActionController::class, 'forceDestroy'])->whereUuid('air');
        Route::put('/air/{air}/inspection', [AirInspectionController::class, 'save'])->whereUuid('air');
        Route::put('/air/{air}/inspection/finalize', [AirInspectionController::class, 'finalize'])->whereUuid('air');
        Route::post('/air/{air}/inspection/reopen', [AirInspectionController::class, 'reopen'])->whereUuid('air');
        Route::get('/air/{air}/inventory/eligible', [AirInventoryPromotionController::class, 'eligible'])->whereUuid('air');
        Route::post('/air/{air}/inventory/promote', [AirInventoryPromotionController::class, 'promote'])->whereUuid('air');
        Route::get('/air/{air}/inspection/items/{airItem}/units', [AirInspectionUnitController::class, 'index'])->whereUuid(['air', 'airItem']);
        Route::put('/air/{air}/inspection/items/{airItem}/units', [AirInspectionUnitController::class, 'save'])->whereUuid(['air', 'airItem']);
        Route::delete('/air/{air}/inspection/items/{airItem}/units/{unit}', [AirInspectionUnitController::class, 'destroy'])->whereUuid(['air', 'airItem', 'unit']);
        Route::get('/air/{air}/inspection/items/{airItem}/units/{unit}/files', [AirInspectionUnitFileController::class, 'index'])->whereUuid(['air', 'airItem', 'unit']);
        Route::post('/air/{air}/inspection/items/{airItem}/units/{unit}/files', [AirInspectionUnitFileController::class, 'store'])->whereUuid(['air', 'airItem', 'unit']);
        Route::get('/air/{air}/inspection/items/{airItem}/units/{unit}/files/{file}/preview', [AirInspectionUnitFileController::class, 'preview'])->whereUuid(['air', 'airItem', 'unit', 'file']);
        Route::delete('/air/{air}/inspection/items/{airItem}/units/{unit}/files/{file}', [AirInspectionUnitFileController::class, 'destroy'])->whereUuid(['air', 'airItem', 'unit', 'file']);
        Route::put('/air/{air}/inspection/items/{airItem}/units/{unit}/files/{file}/primary', [AirInspectionUnitFileController::class, 'setPrimary'])->whereUuid(['air', 'airItem', 'unit', 'file']);
        Route::post('/stocks/adjust', [StockController::class, 'adjust']);
    });
});
