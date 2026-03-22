<?php

use App\Modules\GSO\Http\Controllers\AccountableOfficers\AccountableOfficerActionController;
use App\Modules\GSO\Http\Controllers\AccountableOfficers\AccountableOfficerController;
use App\Modules\GSO\Http\Controllers\Air\AirActionController;
use App\Modules\GSO\Http\Controllers\Air\AirController;
use App\Modules\GSO\Http\Controllers\Air\AirFileController;
use App\Modules\GSO\Http\Controllers\Air\AirInventoryPromotionController;
use App\Modules\GSO\Http\Controllers\Air\AirInspectionController;
use App\Modules\GSO\Http\Controllers\Air\AirInspectionUnitController;
use App\Modules\GSO\Http\Controllers\Air\AirInspectionUnitFileController;
use App\Modules\GSO\Http\Controllers\Air\AirItemController;
use App\Modules\GSO\Http\Controllers\Air\AirPrintController;
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
use App\Modules\GSO\Http\Controllers\Inspections\InspectionActionController;
use App\Modules\GSO\Http\Controllers\Inspections\InspectionController;
use App\Modules\GSO\Http\Controllers\Inspections\InspectionPhotoController;
use App\Modules\GSO\Http\Controllers\InventoryItems\InventoryItemActionController;
use App\Modules\GSO\Http\Controllers\InventoryItems\InventoryItemBatchPropertyCardController;
use App\Modules\GSO\Http\Controllers\InventoryItems\InventoryItemController;
use App\Modules\GSO\Http\Controllers\InventoryItems\InventoryItemEventController;
use App\Modules\GSO\Http\Controllers\InventoryItems\InventoryItemFileController;
use App\Modules\GSO\Http\Controllers\InventoryItems\InventoryItemPropertyCardController;
use App\Modules\GSO\Http\Controllers\InventoryItems\InventoryItemReportsController;
use App\Modules\GSO\Http\Controllers\InventoryItems\PublicInventoryAssetController;
use App\Modules\GSO\Http\Controllers\Items\ItemActionController;
use App\Modules\GSO\Http\Controllers\Items\ItemController;
use App\Modules\GSO\Http\Controllers\Stocks\StockController;
use App\Core\Http\Controllers\Access\PermissionController;
use App\Core\Http\Controllers\Access\RolesController;
use App\Core\Http\Controllers\Access\UserAccessController;
use App\Core\Http\Controllers\AuditLogs\AuditLogController;
use App\Core\Http\Controllers\AuditLogs\AuditLogPrintController;
use App\Core\Http\Controllers\AuditLogs\AuditRestoreController;
use Illuminate\Support\Facades\Route;

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

            Route::get('/air', [AirController::class, 'index'])->name('air.index');
            Route::get('/air/data', [AirController::class, 'data'])->name('air.data');
            Route::get('/air/create', [AirController::class, 'create'])->name('air.create');
            Route::get('/air/{air}/edit', [AirController::class, 'edit'])->whereUuid('air')->name('air.edit');
            Route::put('/air/{air}', [AirActionController::class, 'update'])->whereUuid('air')->name('air.update');
            Route::put('/air/{air}/submit', [AirActionController::class, 'submit'])->whereUuid('air')->name('air.submit');
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
            Route::get('/air/{air}/print', [AirPrintController::class, 'print'])->whereUuid('air')->name('air.print');
            Route::get('/air/{air}/files', [AirFileController::class, 'index'])->whereUuid('air')->name('air.files.index');
            Route::post('/air/{air}/files', [AirFileController::class, 'store'])->whereUuid('air')->name('air.files.store');
            Route::get('/air/{air}/files/{file}/preview', [AirFileController::class, 'preview'])->whereUuid(['air', 'file'])->name('air.files.preview');
            Route::delete('/air/{air}/files/{file}', [AirFileController::class, 'destroy'])->whereUuid(['air', 'file'])->name('air.files.destroy');
            Route::put('/air/{air}/files/{file}/primary', [AirFileController::class, 'setPrimary'])->whereUuid(['air', 'file'])->name('air.files.set-primary');
            Route::put('/air/{air}/inspection', [AirInspectionController::class, 'save'])->whereUuid('air')->name('air.inspection.save');
            Route::put('/air/{air}/inspection/finalize', [AirInspectionController::class, 'finalize'])->whereUuid('air')->name('air.inspection.finalize');
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
            Route::get('/items', [ItemController::class, 'index'])->name('items.index');
            Route::get('/items/data', [ItemController::class, 'data'])->name('items.data');
            Route::get('/items/{item}', [ItemActionController::class, 'show'])->whereUuid('item')->name('items.show');
            Route::post('/items', [ItemActionController::class, 'store'])->name('items.store');
            Route::put('/items/{item}', [ItemActionController::class, 'update'])->whereUuid('item')->name('items.update');
            Route::delete('/items/{item}', [ItemActionController::class, 'destroy'])->whereUuid('item')->name('items.destroy');
            Route::patch('/items/{item}/restore', [ItemActionController::class, 'restore'])->whereUuid('item')->name('items.restore');
            Route::get('/inventory-items', [InventoryItemController::class, 'index'])->name('inventory-items.index');
            Route::get('/inventory-items/data', [InventoryItemController::class, 'data'])->name('inventory-items.data');
            Route::get('/inventory-items/{inventoryItem}', [InventoryItemActionController::class, 'show'])->whereUuid('inventoryItem')->name('inventory-items.show');
            Route::post('/inventory-items', [InventoryItemActionController::class, 'store'])->name('inventory-items.store');
            Route::put('/inventory-items/{inventoryItem}', [InventoryItemActionController::class, 'update'])->whereUuid('inventoryItem')->name('inventory-items.update');
            Route::delete('/inventory-items/{inventoryItem}', [InventoryItemActionController::class, 'destroy'])->whereUuid('inventoryItem')->name('inventory-items.destroy');
            Route::patch('/inventory-items/{inventoryItem}/restore', [InventoryItemActionController::class, 'restore'])->whereUuid('inventoryItem')->name('inventory-items.restore');
            Route::get('/inventory-items/{inventoryItem}/files', [InventoryItemFileController::class, 'index'])->whereUuid('inventoryItem')->name('inventory-items.files.index');
            Route::post('/inventory-items/{inventoryItem}/files', [InventoryItemFileController::class, 'store'])->whereUuid('inventoryItem')->name('inventory-items.files.store');
            Route::post('/inventory-items/{inventoryItem}/files/import-inspection', [InventoryItemFileController::class, 'importInspection'])->whereUuid('inventoryItem')->name('inventory-items.files.import-inspection');
            Route::get('/inventory-items/{inventoryItem}/files/{file}/preview', [InventoryItemFileController::class, 'preview'])
                ->whereUuid(['inventoryItem', 'file'])
                ->name('inventory-items.files.preview');
            Route::delete('/inventory-items/{inventoryItem}/files/{file}', [InventoryItemFileController::class, 'destroy'])
                ->whereUuid(['inventoryItem', 'file'])
                ->name('inventory-items.files.destroy');
            Route::get('/inventory-items/{inventoryItem}/events', [InventoryItemEventController::class, 'index'])->whereUuid('inventoryItem')->name('inventory-items.events.index');
            Route::post('/inventory-items/{inventoryItem}/events', [InventoryItemEventController::class, 'store'])->whereUuid('inventoryItem')->name('inventory-items.events.store');
            Route::get('/inventory-items/{inventoryItem}/property-card/print', [InventoryItemPropertyCardController::class, 'print'])
                ->whereUuid('inventoryItem')
                ->name('inventory-items.property-card.print');
            Route::get('/inventory-items/property-cards/print', [InventoryItemBatchPropertyCardController::class, 'print'])
                ->name('inventory-items.property-cards.print-batch');
            Route::get('/reports/regspi/print', [InventoryItemReportsController::class, 'printRegspi'])
                ->name('inventory-items.regspi.print');
            Route::get('/reports/rpcppe/print', [InventoryItemReportsController::class, 'printRpcppe'])
                ->name('inventory-items.rpcppe.print');
            Route::get('/reports/rpcsp/print', [InventoryItemReportsController::class, 'printRpcsp'])
                ->name('inventory-items.rpcsp.print');
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
            Route::get('/accountable-officers', [AccountableOfficerController::class, 'index'])->name('accountable-officers.index');
            Route::get('/accountable-officers/data', [AccountableOfficerController::class, 'data'])->name('accountable-officers.data');
            Route::get('/accountable-officers/suggest', [AccountableOfficerController::class, 'suggest'])->name('accountable-officers.suggest');
            Route::post('/accountable-officers', [AccountableOfficerActionController::class, 'store'])->name('accountable-officers.store');
            Route::put('/accountable-officers/{accountableOfficer}', [AccountableOfficerActionController::class, 'update'])->whereUuid('accountableOfficer')->name('accountable-officers.update');
            Route::delete('/accountable-officers/{accountableOfficer}', [AccountableOfficerActionController::class, 'destroy'])->whereUuid('accountableOfficer')->name('accountable-officers.destroy');
            Route::patch('/accountable-officers/{accountableOfficer}/restore', [AccountableOfficerActionController::class, 'restore'])->whereUuid('accountableOfficer')->name('accountable-officers.restore');
        });

    Route::get('/air', function () {
        return redirect()->route('gso.air.index');
    });
    Route::get('/air/{air}/edit', function (string $air) {
        return redirect()->route('gso.air.edit', ['air' => $air] + request()->query());
    })->whereUuid('air');
    Route::get('/air/{air}/inspect', function (string $air) {
        return redirect()->route('gso.air.inspect', ['air' => $air] + request()->query());
    })->whereUuid('air');
    Route::get('/air/{air}/print', function (string $air) {
        return redirect()->route('gso.air.print', ['air' => $air] + request()->query());
    })->whereUuid('air')->name('gso.air.legacy.print');
    Route::redirect('/items', '/gso/items');
    Route::redirect('/inventory-items', '/gso/inventory-items');
    Route::get('/inventory-items/{inventoryItem}/property-card/print', function (string $inventoryItem) {
        return redirect()->route('gso.inventory-items.property-card.print', [
            'inventoryItem' => $inventoryItem,
            'preview' => request()->query('preview'),
        ]);
    })->whereUuid('inventoryItem');
    Route::get('/inventory-items/property-cards/print', function () {
        return redirect()->route('gso.inventory-items.property-cards.print-batch', request()->query());
    });
    Route::get('/reports/regspi/print', function () {
        return redirect()->route('gso.inventory-items.regspi.print', request()->query());
    });
    Route::get('/reports/rpcppe/print', function () {
        return redirect()->route('gso.inventory-items.rpcppe.print', request()->query());
    });
    Route::get('/reports/rpcsp/print', function () {
        return redirect()->route('gso.inventory-items.rpcsp.print', request()->query());
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
    Route::redirect('/accountable-officers', '/gso/accountable-officers');

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
