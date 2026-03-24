<?php

namespace App\Modules\GSO\Providers;

use App\Modules\GSO\Builders\AccountableOfficerDatatableRowBuilder;
use App\Modules\GSO\Builders\Air\AirDatatableRowBuilder;
use App\Modules\GSO\Builders\AssetCategoryDatatableRowBuilder;
use App\Modules\GSO\Builders\AssetTypeDatatableRowBuilder;
use App\Modules\GSO\Builders\DepartmentDatatableRowBuilder;
use App\Modules\GSO\Builders\FundClusterDatatableRowBuilder;
use App\Modules\GSO\Builders\FundSourceDatatableRowBuilder;
use App\Modules\GSO\Builders\InspectionDatatableRowBuilder;
use App\Modules\GSO\Builders\InventoryItemDatatableRowBuilder;
use App\Modules\GSO\Builders\ItemDatatableRowBuilder;
use App\Modules\GSO\Builders\StockDatatableRowBuilder;
use App\Modules\GSO\Builders\Contracts\AccountableOfficerDatatableRowBuilderInterface;
use App\Modules\GSO\Builders\Contracts\Air\AirDatatableRowBuilderInterface;
use App\Modules\GSO\Builders\Contracts\AssetCategoryDatatableRowBuilderInterface;
use App\Modules\GSO\Builders\Contracts\AssetTypeDatatableRowBuilderInterface;
use App\Modules\GSO\Builders\Contracts\DepartmentDatatableRowBuilderInterface;
use App\Modules\GSO\Builders\Contracts\FundClusterDatatableRowBuilderInterface;
use App\Modules\GSO\Builders\Contracts\FundSourceDatatableRowBuilderInterface;
use App\Modules\GSO\Builders\Contracts\InspectionDatatableRowBuilderInterface;
use App\Modules\GSO\Builders\Contracts\InventoryItemDatatableRowBuilderInterface;
use App\Modules\GSO\Builders\Contracts\ItemDatatableRowBuilderInterface;
use App\Modules\GSO\Builders\Contracts\StockDatatableRowBuilderInterface;
use App\Modules\GSO\Repositories\Contracts\AccountableOfficerRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\AirFileRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\AirItemRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\AirItemUnitFileRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\AirItemUnitRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\AirRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\AssetCategoryRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\AssetTypeRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\DepartmentRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\FundClusterRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\FundSourceRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\InspectionPhotoRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\InspectionRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\InventoryItemEventRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\InventoryItemFileRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\InventoryItemRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\ItemRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\ItemUnitConversionRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\RIS\RisItemRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\RIS\RisRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\StockMovementRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\StockRepositoryInterface;
use App\Modules\GSO\Repositories\Eloquent\EloquentAccountableOfficerRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentAirFileRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentAirItemRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentAirItemUnitFileRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentAirItemUnitRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentAirRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentAssetCategoryRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentAssetTypeRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentDepartmentRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentFundClusterRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentFundSourceRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentInspectionPhotoRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentInspectionRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentInventoryItemEventRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentInventoryItemFileRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentInventoryItemRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentItemRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentItemUnitConversionRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentStockMovementRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentStockRepository;
use App\Modules\GSO\Repositories\Eloquent\RIS\EloquentRisItemRepository;
use App\Modules\GSO\Repositories\Eloquent\RIS\EloquentRisRepository;
use App\Modules\GSO\Services\AccountableOfficerService;
use App\Modules\GSO\Services\Air\AirFileService;
use App\Modules\GSO\Services\Air\AirInspectionService;
use App\Modules\GSO\Services\Air\AirInspectionUnitFileService;
use App\Modules\GSO\Services\Air\AirInspectionUnitService;
use App\Modules\GSO\Services\Air\AirInventoryPromotionService;
use App\Modules\GSO\Services\Air\AirItemService;
use App\Modules\GSO\Services\Air\AirPrintService;
use App\Modules\GSO\Services\Air\AirService;
use App\Modules\GSO\Services\AssetCategoryService;
use App\Modules\GSO\Services\AssetTypeService;
use App\Modules\GSO\Services\Contracts\AccountableOfficerServiceInterface;
use App\Modules\GSO\Services\Contracts\Air\AirFileServiceInterface;
use App\Modules\GSO\Services\Contracts\Air\AirInspectionServiceInterface;
use App\Modules\GSO\Services\Contracts\Air\AirInspectionUnitFileServiceInterface;
use App\Modules\GSO\Services\Contracts\Air\AirInspectionUnitServiceInterface;
use App\Modules\GSO\Services\Contracts\Air\AirInventoryPromotionServiceInterface;
use App\Modules\GSO\Services\Contracts\Air\AirItemServiceInterface;
use App\Modules\GSO\Services\Contracts\Air\AirPrintServiceInterface;
use App\Modules\GSO\Services\Contracts\Air\AirServiceInterface;
use App\Modules\GSO\Services\Contracts\AssetCategoryServiceInterface;
use App\Modules\GSO\Services\Contracts\AssetTypeServiceInterface;
use App\Modules\GSO\Services\Contracts\DepartmentServiceInterface;
use App\Modules\GSO\Services\Contracts\FundClusterServiceInterface;
use App\Modules\GSO\Services\Contracts\FundSourceServiceInterface;
use App\Modules\GSO\Services\Contracts\InspectionPhotoServiceInterface;
use App\Modules\GSO\Services\Contracts\InspectionServiceInterface;
use App\Modules\GSO\Services\Contracts\InventoryItemCardPrintServiceInterface;
use App\Modules\GSO\Services\Contracts\InventoryItemEventServiceInterface;
use App\Modules\GSO\Services\Contracts\InventoryItemFileServiceInterface;
use App\Modules\GSO\Services\Contracts\InventoryItemPublicAssetServiceInterface;
use App\Modules\GSO\Services\Contracts\InventoryItemServiceInterface;
use App\Modules\GSO\Services\Contracts\ItemServiceInterface;
use App\Modules\GSO\Services\Contracts\RegspiReportServiceInterface;
use App\Modules\GSO\Services\Contracts\RpcppeReportServiceInterface;
use App\Modules\GSO\Services\Contracts\RpcspReportServiceInterface;
use App\Modules\GSO\Services\Contracts\StockServiceInterface;
use App\Modules\GSO\Services\DepartmentService;
use App\Modules\GSO\Services\FundClusterService;
use App\Modules\GSO\Services\FundSourceService;
use App\Modules\GSO\Services\InspectionPhotoService;
use App\Modules\GSO\Services\InspectionService;
use App\Modules\GSO\Services\InventoryItemCardPrintService;
use App\Modules\GSO\Services\InventoryItemEventService;
use App\Modules\GSO\Services\InventoryItemFileService;
use App\Modules\GSO\Services\InventoryItemPublicAssetService;
use App\Modules\GSO\Services\InventoryItemService;
use App\Modules\GSO\Services\ItemService;
use App\Modules\GSO\Services\RegspiReportService;
use App\Modules\GSO\Services\RpcppeReportService;
use App\Modules\GSO\Services\RpcspReportService;
use App\Modules\GSO\Services\StockService;
use Illuminate\Support\ServiceProvider;

class GsoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerRepositories();
        $this->registerApplicationBuilders();
        $this->registerApplicationServices();
    }

    public function boot(): void
    {
        $this->loadViewsFrom(resource_path('modules/gso/views'), 'gso');
    }

    private function registerRepositories(): void
    {
        $this->bindMany([
            /* Asset Catalog */
            AssetTypeRepositoryInterface::class => EloquentAssetTypeRepository::class,
            AssetCategoryRepositoryInterface::class => EloquentAssetCategoryRepository::class,
            ItemRepositoryInterface::class => EloquentItemRepository::class,
            ItemUnitConversionRepositoryInterface::class => EloquentItemUnitConversionRepository::class,

            /* AIR */
            AirRepositoryInterface::class => EloquentAirRepository::class,
            AirFileRepositoryInterface::class => EloquentAirFileRepository::class,
            AirItemRepositoryInterface::class => EloquentAirItemRepository::class,
            AirItemUnitRepositoryInterface::class => EloquentAirItemUnitRepository::class,
            AirItemUnitFileRepositoryInterface::class => EloquentAirItemUnitFileRepository::class,

            /* Organizational References */
            DepartmentRepositoryInterface::class => EloquentDepartmentRepository::class,
            FundClusterRepositoryInterface::class => EloquentFundClusterRepository::class,
            FundSourceRepositoryInterface::class => EloquentFundSourceRepository::class,
            AccountableOfficerRepositoryInterface::class => EloquentAccountableOfficerRepository::class,

            /* Inspection And Inventory */
            InspectionRepositoryInterface::class => EloquentInspectionRepository::class,
            InspectionPhotoRepositoryInterface::class => EloquentInspectionPhotoRepository::class,
            InventoryItemRepositoryInterface::class => EloquentInventoryItemRepository::class,
            InventoryItemEventRepositoryInterface::class => EloquentInventoryItemEventRepository::class,
            InventoryItemFileRepositoryInterface::class => EloquentInventoryItemFileRepository::class,

            /* Stock */
            StockRepositoryInterface::class => EloquentStockRepository::class,
            StockMovementRepositoryInterface::class => EloquentStockMovementRepository::class,

            /* RIS */
            RisRepositoryInterface::class => EloquentRisRepository::class,
            RisItemRepositoryInterface::class => EloquentRisItemRepository::class,
        ]);
    }

    private function registerApplicationBuilders(): void
    {
        $this->bindMany([
            /* Asset Catalog */
            AssetTypeDatatableRowBuilderInterface::class => AssetTypeDatatableRowBuilder::class,
            AssetCategoryDatatableRowBuilderInterface::class => AssetCategoryDatatableRowBuilder::class,
            ItemDatatableRowBuilderInterface::class => ItemDatatableRowBuilder::class,

            /* AIR */
            AirDatatableRowBuilderInterface::class => AirDatatableRowBuilder::class,

            /* Organizational References */
            DepartmentDatatableRowBuilderInterface::class => DepartmentDatatableRowBuilder::class,
            FundClusterDatatableRowBuilderInterface::class => FundClusterDatatableRowBuilder::class,
            FundSourceDatatableRowBuilderInterface::class => FundSourceDatatableRowBuilder::class,
            AccountableOfficerDatatableRowBuilderInterface::class => AccountableOfficerDatatableRowBuilder::class,

            /* Inspection And Inventory */
            InspectionDatatableRowBuilderInterface::class => InspectionDatatableRowBuilder::class,
            InventoryItemDatatableRowBuilderInterface::class => InventoryItemDatatableRowBuilder::class,

            /* Stock */
            StockDatatableRowBuilderInterface::class => StockDatatableRowBuilder::class,
        ]);
    }

    private function registerApplicationServices(): void
    {
        $this->bindMany([
            /* Asset Catalog */
            AssetTypeServiceInterface::class => AssetTypeService::class,
            AssetCategoryServiceInterface::class => AssetCategoryService::class,
            ItemServiceInterface::class => ItemService::class,

            /* AIR */
            AirServiceInterface::class => AirService::class,
            AirFileServiceInterface::class => AirFileService::class,
            AirItemServiceInterface::class => AirItemService::class,
            AirInspectionServiceInterface::class => AirInspectionService::class,
            AirInspectionUnitServiceInterface::class => AirInspectionUnitService::class,
            AirInspectionUnitFileServiceInterface::class => AirInspectionUnitFileService::class,
            AirInventoryPromotionServiceInterface::class => AirInventoryPromotionService::class,
            AirPrintServiceInterface::class => AirPrintService::class,

            /* Organizational References */
            DepartmentServiceInterface::class => DepartmentService::class,
            FundClusterServiceInterface::class => FundClusterService::class,
            FundSourceServiceInterface::class => FundSourceService::class,
            AccountableOfficerServiceInterface::class => AccountableOfficerService::class,

            /* Inspection And Inventory */
            InspectionServiceInterface::class => InspectionService::class,
            InspectionPhotoServiceInterface::class => InspectionPhotoService::class,
            InventoryItemServiceInterface::class => InventoryItemService::class,
            InventoryItemEventServiceInterface::class => InventoryItemEventService::class,
            InventoryItemFileServiceInterface::class => InventoryItemFileService::class,
            InventoryItemPublicAssetServiceInterface::class => InventoryItemPublicAssetService::class,
            InventoryItemCardPrintServiceInterface::class => InventoryItemCardPrintService::class,

            /* Stock */
            StockServiceInterface::class => StockService::class,

            /* Reports */
            RegspiReportServiceInterface::class => RegspiReportService::class,
            RpcppeReportServiceInterface::class => RpcppeReportService::class,
            RpcspReportServiceInterface::class => RpcspReportService::class,
        ], true);
    }

    /**
     * @param  array<class-string, class-string>  $map
     */
    private function bindMany(array $map, bool $asSingleton = false): void
    {
        foreach ($map as $abstract => $concrete) {
            if ($asSingleton) {
                $this->app->singleton($abstract, $concrete);
                continue;
            }

            $this->app->bind($abstract, $concrete);
        }
    }
}