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
use App\Modules\GSO\Data\Contracts\RIS\RisItemDataProviderInterface;
use App\Modules\GSO\Data\RIS\RisItemDataProvider;
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
use App\Modules\GSO\Repositories\Contracts\ICS\IcsItemRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\ICS\IcsRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\ITR\ItrItemRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\ITR\ItrRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\ItemRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\ItemUnitConversionRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\PAR\ParItemRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\PAR\ParRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\PTR\PtrItemRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\PTR\PtrRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\RIS\RisItemRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\RIS\RisRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\StockMovementRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\StockRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\WMR\WmrItemRepositoryInterface;
use App\Modules\GSO\Repositories\Contracts\WMR\WmrRepositoryInterface;
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
use App\Modules\GSO\Repositories\Eloquent\ICS\EloquentIcsItemRepository;
use App\Modules\GSO\Repositories\Eloquent\ICS\EloquentIcsRepository;
use App\Modules\GSO\Repositories\Eloquent\ITR\EloquentItrItemRepository;
use App\Modules\GSO\Repositories\Eloquent\ITR\EloquentItrRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentItemRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentItemUnitConversionRepository;
use App\Modules\GSO\Repositories\Eloquent\PAR\EloquentParItemRepository;
use App\Modules\GSO\Repositories\Eloquent\PAR\EloquentParRepository;
use App\Modules\GSO\Repositories\Eloquent\PTR\EloquentPtrItemRepository;
use App\Modules\GSO\Repositories\Eloquent\PTR\EloquentPtrRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentStockMovementRepository;
use App\Modules\GSO\Repositories\Eloquent\EloquentStockRepository;
use App\Modules\GSO\Repositories\Eloquent\RIS\EloquentRisItemRepository;
use App\Modules\GSO\Repositories\Eloquent\RIS\EloquentRisRepository;
use App\Modules\GSO\Repositories\Eloquent\WMR\EloquentWmrItemRepository;
use App\Modules\GSO\Repositories\Eloquent\WMR\EloquentWmrRepository;
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
use App\Modules\GSO\Services\Contracts\GsoSignedDocumentArchiveServiceInterface;
use App\Modules\GSO\Services\Contracts\GsoStorageSettingsServiceInterface;
use App\Modules\GSO\Services\Contracts\GsoDashboardServiceInterface;
use App\Modules\GSO\Services\Contracts\InspectionPhotoServiceInterface;
use App\Modules\GSO\Services\Contracts\InspectionServiceInterface;
use App\Modules\GSO\Services\Contracts\InventoryItemCardPrintServiceInterface;
use App\Modules\GSO\Services\Contracts\InventoryItemEventServiceInterface;
use App\Modules\GSO\Services\Contracts\InventoryItemFileServiceInterface;
use App\Modules\GSO\Services\Contracts\InventoryItemPublicAssetServiceInterface;
use App\Modules\GSO\Services\Contracts\InventoryItemServiceInterface;
use App\Modules\GSO\Services\Contracts\ICS\IcsItemServiceInterface;
use App\Modules\GSO\Services\Contracts\ICS\IcsPrintServiceInterface;
use App\Modules\GSO\Services\Contracts\ICS\IcsServiceInterface;
use App\Modules\GSO\Services\Contracts\ICS\IcsWorkflowServiceInterface;
use App\Modules\GSO\Services\Contracts\ITR\ItrItemServiceInterface;
use App\Modules\GSO\Services\Contracts\ITR\ItrPrintServiceInterface;
use App\Modules\GSO\Services\Contracts\ITR\ItrServiceInterface;
use App\Modules\GSO\Services\Contracts\ITR\ItrWorkflowServiceInterface;
use App\Modules\GSO\Services\Contracts\ItemServiceInterface;
use App\Modules\GSO\Services\Contracts\Numbers\IcsNumberServiceInterface;
use App\Modules\GSO\Services\Contracts\Numbers\ItrNumberServiceInterface;
use App\Modules\GSO\Services\Contracts\Numbers\ParNumberServiceInterface;
use App\Modules\GSO\Services\Contracts\Numbers\PtrNumberServiceInterface;
use App\Modules\GSO\Services\Contracts\Numbers\RisNumberServiceInterface;
use App\Modules\GSO\Services\Contracts\Numbers\WmrNumberServiceInterface;
use App\Modules\GSO\Services\Contracts\PAR\ParItemServiceInterface;
use App\Modules\GSO\Services\Contracts\PAR\ParPrintServiceInterface;
use App\Modules\GSO\Services\Contracts\PAR\ParServiceInterface;
use App\Modules\GSO\Services\Contracts\PAR\ParWorkflowServiceInterface;
use App\Modules\GSO\Services\Contracts\PropertyCardsReportServiceInterface;
use App\Modules\GSO\Services\Contracts\StickerDirectPdfServiceInterface;
use App\Modules\GSO\Services\Contracts\StickerReportServiceInterface;
use App\Modules\GSO\Services\Contracts\PTR\PtrItemServiceInterface;
use App\Modules\GSO\Services\Contracts\PTR\PtrPrintServiceInterface;
use App\Modules\GSO\Services\Contracts\PTR\PtrServiceInterface;
use App\Modules\GSO\Services\Contracts\PTR\PtrWorkflowServiceInterface;
use App\Modules\GSO\Services\PAR\ParItemService;
use App\Modules\GSO\Services\PAR\ParPrintService;
use App\Modules\GSO\Services\PAR\ParService;
use App\Modules\GSO\Services\PAR\ParWorkflowService;
use App\Modules\GSO\Services\Numbers\ParNumberService;
use App\Modules\GSO\Services\Numbers\PtrNumberService;
use App\Modules\GSO\Services\PTR\PtrItemService;
use App\Modules\GSO\Services\PTR\PtrPrintService;
use App\Modules\GSO\Services\PTR\PtrService;
use App\Modules\GSO\Services\PTR\PtrWorkflowService;
use App\Modules\GSO\Services\Contracts\RIS\RisItemServiceInterface;
use App\Modules\GSO\Services\RIS\RisItemService;
use App\Modules\GSO\Services\Contracts\RIS\RisPrintServiceInterface;
use App\Modules\GSO\Services\Contracts\RIS\RisServiceInterface;
use App\Modules\GSO\Services\Contracts\RIS\RisWorkflowServiceInterface;
use App\Modules\GSO\Services\Numbers\RisNumberService;
use App\Modules\GSO\Services\RIS\RisPrintService;
use App\Modules\GSO\Services\RIS\RisService;
use App\Modules\GSO\Services\RIS\RisWorkflowService;
use App\Modules\GSO\Services\Contracts\RegspiReportServiceInterface;
use App\Modules\GSO\Services\Contracts\RspiReportServiceInterface;
use App\Modules\GSO\Services\Contracts\RrspReportServiceInterface;
use App\Modules\GSO\Services\Contracts\RpcppeReportServiceInterface;
use App\Modules\GSO\Services\Contracts\RpcspReportServiceInterface;
use App\Modules\GSO\Services\Contracts\StockServiceInterface;
use App\Modules\GSO\Services\Contracts\WMR\WmrItemServiceInterface;
use App\Modules\GSO\Services\Contracts\WMR\WmrPrintServiceInterface;
use App\Modules\GSO\Services\Contracts\WMR\WmrServiceInterface;
use App\Modules\GSO\Services\Contracts\WMR\WmrWorkflowServiceInterface;
use App\Modules\GSO\Services\DepartmentService;
use App\Modules\GSO\Services\FundClusterService;
use App\Modules\GSO\Services\FundSourceService;
use App\Modules\GSO\Services\GsoDashboardService;
use App\Modules\GSO\Services\GsoSignedDocumentArchiveService;
use App\Modules\GSO\Services\GsoStorageSettingsService;
use App\Modules\GSO\Services\InspectionPhotoService;
use App\Modules\GSO\Services\InspectionService;
use App\Modules\GSO\Services\InventoryItemCardPrintService;
use App\Modules\GSO\Services\InventoryItemEventService;
use App\Modules\GSO\Services\InventoryItemFileService;
use App\Modules\GSO\Services\InventoryItemPublicAssetService;
use App\Modules\GSO\Services\InventoryItemService;
use App\Modules\GSO\Services\ICS\IcsItemService;
use App\Modules\GSO\Services\ICS\IcsPrintService;
use App\Modules\GSO\Services\ICS\IcsService;
use App\Modules\GSO\Services\ICS\IcsWorkflowService;
use App\Modules\GSO\Services\ITR\ItrItemService;
use App\Modules\GSO\Services\ITR\ItrPrintService;
use App\Modules\GSO\Services\ITR\ItrService;
use App\Modules\GSO\Services\ITR\ItrWorkflowService;
use App\Modules\GSO\Services\ItemService;
use App\Modules\GSO\Services\Numbers\IcsNumberService;
use App\Modules\GSO\Services\Numbers\ItrNumberService;
use App\Modules\GSO\Services\Numbers\WmrNumberService;
use App\Modules\GSO\Services\PropertyCardsReportService;
use App\Modules\GSO\Services\StickerDirectPdfService;
use App\Modules\GSO\Services\StickerReportService;
use App\Modules\GSO\Services\RegspiReportService;
use App\Modules\GSO\Services\RspiReportService;
use App\Modules\GSO\Services\RrspReportService;
use App\Modules\GSO\Services\RpcppeReportService;
use App\Modules\GSO\Services\RpcspReportService;
use App\Modules\GSO\Services\StockService;
use App\Modules\GSO\Services\WMR\WmrItemService;
use App\Modules\GSO\Services\WMR\WmrPrintService;
use App\Modules\GSO\Services\WMR\WmrService;
use App\Modules\GSO\Services\WMR\WmrWorkflowService;
use App\Core\Services\Tasks\Contracts\TaskReadServiceInterface;
use App\Core\Support\CurrentContext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class GsoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerRepositories();
        $this->registerApplicationBuilders();
        $this->registerApplicationData();
        $this->registerApplicationServices();
    }

    public function boot(): void
    {
        $this->loadViewsFrom(resource_path('modules/gso/views'), 'gso');

        View::composer('gso::layouts.components.sidebar-menu', function ($view) {
            $user = Auth::user();
            $taskCounts = null;

            if ($user) {
                $sidebarModule = $view->getData()['moduleSidebarModule'] ?? null;
                $moduleId = trim((string) ($sidebarModule->id ?? app(CurrentContext::class)->moduleId()));
                $cacheKey = 'task_counts:gso:' . $user->id . ':' . $moduleId;

                $taskCounts = Cache::remember($cacheKey, now()->addSeconds(20), function () use ($user, $moduleId) {
                    if ($moduleId === '') {
                        return [
                            'my' => 0,
                            'claimable' => 0,
                        ];
                    }

                    return app(TaskReadServiceInterface::class)->sidebarCounts($user, $moduleId);
                });
            }

            $view->with('gsoTaskCounts', $taskCounts);
        });
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

            /* PAR */
            ParRepositoryInterface::class => EloquentParRepository::class,
            ParItemRepositoryInterface::class => EloquentParItemRepository::class,

            /* PTR */
            PtrRepositoryInterface::class => EloquentPtrRepository::class,
            PtrItemRepositoryInterface::class => EloquentPtrItemRepository::class,

            /* ICS */
            IcsRepositoryInterface::class => EloquentIcsRepository::class,
            IcsItemRepositoryInterface::class => EloquentIcsItemRepository::class,

            /* ITR */
            ItrRepositoryInterface::class => EloquentItrRepository::class,
            ItrItemRepositoryInterface::class => EloquentItrItemRepository::class,

            /* WMR */
            WmrRepositoryInterface::class => EloquentWmrRepository::class,
            WmrItemRepositoryInterface::class => EloquentWmrItemRepository::class,
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

    private function registerApplicationData(): void
    {
        $this->bindMany([
            /* RIS */
            RisItemDataProviderInterface::class => RisItemDataProvider::class,
        ]);
    }

    private function registerApplicationServices(): void
    {
        $this->bindMany([
            GsoDashboardServiceInterface::class => GsoDashboardService::class,
            GsoSignedDocumentArchiveServiceInterface::class => GsoSignedDocumentArchiveService::class,
            GsoStorageSettingsServiceInterface::class => GsoStorageSettingsService::class,

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

            /* RIS */
            RisItemServiceInterface::class => RisItemService::class,
            RisPrintServiceInterface::class => RisPrintService::class,
            RisServiceInterface::class => RisService::class,
            RisWorkflowServiceInterface::class => RisWorkflowService::class,
            RisNumberServiceInterface::class => RisNumberService::class,

            /* PAR */
            ParItemServiceInterface::class => ParItemService::class,
            ParPrintServiceInterface::class => ParPrintService::class,
            ParServiceInterface::class => ParService::class,
            ParWorkflowServiceInterface::class => ParWorkflowService::class,
            ParNumberServiceInterface::class => ParNumberService::class,

            /* PTR */
            PtrItemServiceInterface::class => PtrItemService::class,
            PtrPrintServiceInterface::class => PtrPrintService::class,
            PtrServiceInterface::class => PtrService::class,
            PtrWorkflowServiceInterface::class => PtrWorkflowService::class,
            PtrNumberServiceInterface::class => PtrNumberService::class,

            /* ICS */
            IcsItemServiceInterface::class => IcsItemService::class,
            IcsPrintServiceInterface::class => IcsPrintService::class,
            IcsServiceInterface::class => IcsService::class,
            IcsWorkflowServiceInterface::class => IcsWorkflowService::class,
            IcsNumberServiceInterface::class => IcsNumberService::class,

            /* ITR */
            ItrItemServiceInterface::class => ItrItemService::class,
            ItrPrintServiceInterface::class => ItrPrintService::class,
            ItrServiceInterface::class => ItrService::class,
            ItrWorkflowServiceInterface::class => ItrWorkflowService::class,
            ItrNumberServiceInterface::class => ItrNumberService::class,

            /* WMR */
            WmrItemServiceInterface::class => WmrItemService::class,
            WmrPrintServiceInterface::class => WmrPrintService::class,
            WmrServiceInterface::class => WmrService::class,
            WmrWorkflowServiceInterface::class => WmrWorkflowService::class,
            WmrNumberServiceInterface::class => WmrNumberService::class,

            /* Reports */
            PropertyCardsReportServiceInterface::class => PropertyCardsReportService::class,
            StickerDirectPdfServiceInterface::class => StickerDirectPdfService::class,
            StickerReportServiceInterface::class => StickerReportService::class,
            RegspiReportServiceInterface::class => RegspiReportService::class,
            RspiReportServiceInterface::class => RspiReportService::class,
            RrspReportServiceInterface::class => RrspReportService::class,
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
