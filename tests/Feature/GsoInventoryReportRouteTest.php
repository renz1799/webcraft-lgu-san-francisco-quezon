<?php

namespace Tests\Feature;

use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Repositories\Contracts\InventoryItemRepositoryInterface;
use App\Modules\GSO\Services\Contracts\InventoryItemCardPrintServiceInterface;
use App\Modules\GSO\Services\Contracts\RegspiReportServiceInterface;
use App\Modules\GSO\Services\Contracts\RspiReportServiceInterface;
use App\Modules\GSO\Services\Contracts\RrspReportServiceInterface;
use App\Modules\GSO\Services\Contracts\RpcppeReportServiceInterface;
use App\Modules\GSO\Services\Contracts\RpcspReportServiceInterface;
use App\Modules\GSO\Services\Contracts\StockServiceInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

class GsoInventoryReportRouteTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_legacy_report_route_redirects_to_canonical_gso_path(): void
    {
        $this->withoutMiddleware();

        $response = $this->get('/reports/regspi/print?preview=1&as_of=2026-03-21');

        $response->assertRedirect(route('gso.reports.regspi.print', [
            'preview' => 1,
            'as_of' => '2026-03-21',
        ]));
    }

    public function test_canonical_regspi_route_renders_the_new_report_view(): void
    {
        $this->withoutMiddleware();

        $service = Mockery::mock(RegspiReportServiceInterface::class);
        $service->shouldReceive('getPrintViewData')
            ->once()
            ->andReturn([
                'report' => [
                    'title' => 'Register of Semi-Expendable Property Issued',
                    'document' => [
                        'appendix_label' => 'RegSPI',
                        'fund_source_id' => '',
                        'department_id' => '',
                        'accountable_officer_id' => '',
                        'fund_source' => 'GF-001 - General Appropriation',
                        'fund_cluster' => '101 - General Fund',
                        'department' => 'ICT - ICT Office',
                        'accountable_officer' => 'Juan Dela Cruz',
                        'as_of' => '2026-03-21',
                        'as_of_label' => 'March 21, 2026',
                        'summary' => [
                            'offices_covered' => 1,
                            'accountable_officers_covered' => 1,
                            'total_items' => 1,
                            'total_qty' => 1,
                            'total_value' => 1200,
                        ],
                        'signatories' => [
                            'prepared_by_name' => 'Prep Name',
                            'prepared_by_designation' => 'Prep Designation',
                            'reviewed_by_name' => 'Reviewed Name',
                            'reviewed_by_designation' => 'Reviewed Designation',
                            'approved_by_name' => 'Approved Name',
                            'approved_by_designation' => 'Approved Designation',
                        ],
                    ],
                    'rows' => [[
                        'date' => '2026-03-21',
                        'reference' => 'ICS: ICS-001',
                        'property_no' => 'SE-001',
                        'article' => 'Projector',
                        'description' => 'Office projector',
                        'qty' => 1,
                        'unit_value' => 1200,
                        'office' => 'ICT - ICT Office',
                        'accountable_officer' => 'Juan Dela Cruz',
                        'remarks' => 'Condition: Good',
                        'total_value' => 1200,
                    ]],
                    'pagination' => [
                        'pages' => [[
                            'rows' => [[
                                'date' => '2026-03-21',
                                'reference' => 'ICS: ICS-001',
                                'property_no' => 'SE-001',
                                'article' => 'Projector',
                                'description' => 'Office projector',
                                'qty' => 1,
                                'unit_value' => 1200,
                                'office' => 'ICT - ICT Office',
                                'accountable_officer' => 'Juan Dela Cruz',
                                'remarks' => 'Condition: Good',
                                'total_value' => 1200,
                            ]],
                            'used_units' => 1,
                        ]],
                        'stats' => [
                            'page_count' => 1,
                            'rows_per_page' => 14,
                            'grid_rows' => 14,
                            'last_page_grid_rows' => 8,
                            'description_chars_per_line' => 52,
                            'page_row_counts' => [1],
                            'page_used_units' => [1],
                            'last_page_padding' => 7,
                        ],
                    ],
                ],
                'paperProfile' => [
                    'code' => 'a4-landscape',
                    'width' => '297mm',
                    'height' => '210mm',
                    'preview_width' => '297mm',
                    'rows_per_page' => 14,
                    'grid_rows' => 14,
                    'last_page_grid_rows' => 8,
                    'description_chars_per_line' => 52,
                    'pages_view' => 'gso::reports.regspi.print.paper.a4-landscape.pages',
                    'styles_view' => 'gso::reports.regspi.print.paper.a4-landscape.styles',
                    'pdf_styles_view' => 'gso::reports.regspi.print.paper.a4-landscape.pdf-styles',
                ],
                'available_funds' => [],
                'available_departments' => [],
                'available_accountable_officers' => [],
            ]);

        $this->app->instance(RegspiReportServiceInterface::class, $service);

        $response = $this->get(route('gso.reports.regspi.print', ['preview' => 1]));

        $response->assertOk();
        $response->assertSee('Register of Semi-Expendable Property Issued');
        $response->assertSee('SE-001');
        $response->assertSee('Juan Dela Cruz');
    }

    public function test_legacy_rspi_route_redirects_to_canonical_gso_path(): void
    {
        $this->withoutMiddleware();

        $response = $this->get('/reports/rspi/print?preview=1&date_from=2026-03-01&date_to=2026-03-31');

        $response->assertRedirect(route('gso.reports.rspi.print', [
            'preview' => 1,
            'date_from' => '2026-03-01',
            'date_to' => '2026-03-31',
        ]));
    }

    public function test_canonical_rspi_route_renders_the_new_report_view(): void
    {
        $this->withoutMiddleware();

        $service = Mockery::mock(RspiReportServiceInterface::class);
        $service->shouldReceive('getPrintViewData')
            ->once()
            ->andReturn([
                'report' => [
                    'title' => 'Report of Semi-Expendable Property Issued',
                    'document' => [
                        'appendix_label' => 'RSPI',
                        'fund_source_id' => '',
                        'department_id' => '',
                        'accountable_officer_id' => '',
                        'fund_source' => 'GF-001 - General Appropriation',
                        'fund_cluster' => '101 - General Fund',
                        'department' => 'ICT - ICT Office',
                        'accountable_officer' => 'Juan Dela Cruz',
                        'date_from' => '2026-03-01',
                        'date_to' => '2026-03-31',
                        'period_label' => 'March 1, 2026 to March 31, 2026',
                        'summary' => [
                            'ics_covered' => 1,
                            'lines_count' => 1,
                            'total_qty_issued' => 2,
                            'total_cost' => 2400,
                        ],
                        'signatories' => [
                            'prepared_by_name' => 'Prep Name',
                            'prepared_by_designation' => 'Prep Designation',
                            'reviewed_by_name' => 'Reviewed Name',
                            'reviewed_by_designation' => 'Reviewed Designation',
                            'approved_by_name' => 'Approved Name',
                            'approved_by_designation' => 'Approved Designation',
                        ],
                    ],
                    'rows' => [[
                        'date' => '2026-03-21',
                        'reference' => 'ICS: ICS-001',
                        'property_no' => 'SE-001',
                        'article' => 'Projector',
                        'description' => 'Office projector',
                        'qty_issued' => 2,
                        'unit_cost' => 1200,
                        'office' => 'ICT - ICT Office',
                        'accountable_officer' => 'Juan Dela Cruz',
                        'total_cost' => 2400,
                        'ics_no' => 'ICS-001',
                    ]],
                    'pagination' => [
                        'pages' => [[
                            'rows' => [[
                                'date' => '2026-03-21',
                                'reference' => 'ICS: ICS-001',
                                'property_no' => 'SE-001',
                                'article' => 'Projector',
                                'description' => 'Office projector',
                                'qty_issued' => 2,
                                'unit_cost' => 1200,
                                'office' => 'ICT - ICT Office',
                                'accountable_officer' => 'Juan Dela Cruz',
                                'total_cost' => 2400,
                                'ics_no' => 'ICS-001',
                            ]],
                            'used_units' => 1,
                        ]],
                        'stats' => [
                            'page_count' => 1,
                            'rows_per_page' => 15,
                            'grid_rows' => 15,
                            'last_page_grid_rows' => 8,
                            'description_chars_per_line' => 52,
                            'page_row_counts' => [1],
                            'page_used_units' => [1],
                            'last_page_padding' => 7,
                        ],
                    ],
                ],
                'paperProfile' => [
                    'code' => 'a4-landscape',
                    'width' => '297mm',
                    'height' => '210mm',
                    'preview_width' => '297mm',
                    'rows_per_page' => 15,
                    'grid_rows' => 15,
                    'last_page_grid_rows' => 8,
                    'description_chars_per_line' => 52,
                    'pages_view' => 'gso::reports.rspi.print.paper.a4-landscape.pages',
                    'styles_view' => 'gso::reports.rspi.print.paper.a4-landscape.styles',
                    'pdf_styles_view' => 'gso::reports.rspi.print.paper.a4-landscape.pdf-styles',
                ],
                'available_funds' => [],
                'available_departments' => [],
                'available_accountable_officers' => [],
            ]);

        $this->app->instance(RspiReportServiceInterface::class, $service);

        $response = $this->get(route('gso.reports.rspi.print', ['preview' => 1]));

        $response->assertOk();
        $response->assertSee('Report of Semi-Expendable Property Issued');
        $response->assertSee('SE-001');
        $response->assertSee('Juan Dela Cruz');
    }

    public function test_legacy_rrsp_route_redirects_to_canonical_gso_path(): void
    {
        $this->withoutMiddleware();

        $response = $this->get('/reports/rrsp/print?preview=1&return_date=2026-03-27');

        $response->assertRedirect(route('gso.reports.rrsp.print', [
            'preview' => 1,
            'return_date' => '2026-03-27',
        ]));
    }

    public function test_canonical_rrsp_route_renders_the_new_report_view(): void
    {
        $this->withoutMiddleware();

        $service = Mockery::mock(RrspReportServiceInterface::class);
        $service->shouldReceive('getPrintViewData')
            ->once()
            ->andReturn([
                'report' => [
                    'title' => 'Receipt of Returned Semi-Expendable Property',
                    'document' => [
                        'entity_name' => 'Municipality of San Francisco',
                        'appendix_label' => 'RRSP',
                        'return_date' => '2026-03-27',
                        'return_date_label' => 'March 27, 2026',
                        'summary' => [
                            'items_listed' => 1,
                            'total_qty_returned' => 2,
                            'total_value' => 2400,
                        ],
                        'signatories' => [
                            'returned_by_name' => 'Juan Dela Cruz',
                            'returned_by_designation' => 'Supply Officer',
                            'received_by_name' => 'Ana Reyes',
                            'received_by_designation' => 'GSO Designate',
                            'noted_by_name' => 'Maria Santos',
                            'noted_by_designation' => 'Municipal Mayor',
                        ],
                    ],
                    'rows' => [[
                        'property_no' => 'SE-001',
                        'article' => 'Projector',
                        'description' => 'Office projector',
                        'unit' => 'unit',
                        'qty_returned' => 2,
                        'unit_value' => 1200,
                        'total_value' => 2400,
                        'condition' => 'Serviceable',
                        'office' => 'ICT - ICT Office',
                        'accountable_officer' => 'Juan Dela Cruz',
                        'remarks' => '',
                    ]],
                    'pagination' => [
                        'pages' => [[
                            'rows' => [[
                                'property_no' => 'SE-001',
                                'article' => 'Projector',
                                'description' => 'Office projector',
                                'unit' => 'unit',
                                'qty_returned' => 2,
                                'unit_value' => 1200,
                                'total_value' => 2400,
                                'condition' => 'Serviceable',
                                'office' => 'ICT - ICT Office',
                                'accountable_officer' => 'Juan Dela Cruz',
                                'remarks' => '',
                            ]],
                            'used_units' => 1,
                        ]],
                        'stats' => [
                            'page_count' => 1,
                            'page_used_units' => [1],
                            'last_page_padding' => 0,
                        ],
                    ],
                ],
                'paperProfile' => [
                    'code' => 'a4-landscape',
                    'pages_view' => 'gso::reports.rrsp.print.paper.a4-landscape.pages',
                    'styles_view' => 'gso::reports.rrsp.print.paper.a4-landscape.styles',
                    'pdf_styles_view' => 'gso::reports.rrsp.print.paper.a4-landscape.pdf-styles',
                    'width' => '297mm',
                    'height' => '210mm',
                ],
                'available_funds' => [],
                'available_departments' => [],
                'available_accountable_officers' => [],
            ]);

        $this->app->instance(RrspReportServiceInterface::class, $service);

        $response = $this->get(route('gso.reports.rrsp.print', ['preview' => 1]));

        $response->assertOk();
        $response->assertSee('Receipt of Returned Semi-Expendable Property');
        $response->assertSee('SE-001');
        $response->assertSee('Juan Dela Cruz');
    }

    public function test_canonical_rpcppe_route_renders_the_new_report_view(): void
    {
        $this->withoutMiddleware();

        $service = Mockery::mock(RpcppeReportServiceInterface::class);
        $service->shouldReceive('getPrintViewData')
            ->once()
            ->andReturn([
                'report' => [
                    'title' => 'Report on the Physical Count of Property, Plant and Equipment',
                    'document' => [
                        'entity_name' => 'Municipality of San Francisco',
                        'appendix_label' => 'RPCPPE',
                        'fund_source_id' => 'fund-1',
                        'department_id' => 'dept-1',
                        'accountable_officer_id' => 'officer-1',
                        'fund_source' => 'General Fund',
                        'fund_cluster' => 'FC-01 - General Fund Cluster',
                        'department' => 'GSO - General Services Office',
                        'accountable_officer' => 'Juan Dela Cruz',
                        'as_of' => '2026-03-21',
                        'as_of_label' => 'March 21, 2026',
                        'prefill_count' => false,
                        'summary' => [
                            'offices_covered' => 1,
                            'total_items' => 1,
                            'total_book_value' => 1200,
                            'total_balance_qty' => 1,
                            'total_count_qty' => null,
                            'total_shortage_overage_qty' => null,
                        ],
                        'signatories' => [
                            'accountable_officer_name' => 'Juan Dela Cruz',
                            'accountable_officer_designation' => 'Supply Officer',
                            'committee_chair_name' => 'Ana Reyes',
                            'committee_member_1_name' => '',
                            'committee_member_2_name' => '',
                            'approved_by_name' => 'Maria Santos',
                            'approved_by_designation' => 'Municipal Mayor',
                            'verified_by_name' => 'Pedro Cruz',
                            'verified_by_designation' => 'Municipal Accountant',
                        ],
                    ],
                    'rows' => [[
                        'article' => 'Laptop Computer',
                        'description' => 'Dell Latitude',
                        'property_no' => 'PPE-001',
                        'unit' => 'unit',
                        'unit_value' => 4000,
                        'balance_per_card_qty' => 2,
                        'count_qty' => 2,
                        'shortage_overage_qty' => 0,
                        'shortage_overage_value' => 0,
                        'remarks' => 'Office: GSO - General Services Office',
                    ]],
                    'pagination' => [
                        'pages' => [[
                            'rows' => [[
                                'article' => 'Laptop Computer',
                                'description' => 'Dell Latitude',
                                'property_no' => 'PPE-001',
                                'unit' => 'unit',
                                'unit_value' => 4000,
                                'balance_per_card_qty' => 2,
                                'count_qty' => 2,
                                'shortage_overage_qty' => 0,
                                'shortage_overage_value' => 0,
                                'remarks' => 'Office: GSO - General Services Office',
                            ]],
                            'used_units' => 1,
                        ]],
                        'stats' => [
                            'page_count' => 1,
                            'rows_per_page' => 13,
                            'grid_rows' => 13,
                            'last_page_grid_rows' => 6,
                            'description_chars_per_line' => 44,
                            'page_row_counts' => [1],
                            'page_used_units' => [1],
                            'last_page_padding' => 5,
                        ],
                    ],
                ],
                'paperProfile' => [
                    'code' => 'a4-landscape',
                    'width' => '297mm',
                    'height' => '210mm',
                    'preview_width' => '297mm',
                    'rows_per_page' => 13,
                    'grid_rows' => 13,
                    'last_page_grid_rows' => 6,
                    'description_chars_per_line' => 44,
                    'pages_view' => 'gso::reports.rpcppe.print.paper.a4-landscape.pages',
                    'styles_view' => 'gso::reports.rpcppe.print.paper.a4-landscape.styles',
                    'pdf_styles_view' => 'gso::reports.rpcppe.print.paper.a4-landscape.pdf-styles',
                ],
                'available_funds' => [
                    ['id' => 'fund-1', 'label' => 'General Fund'],
                ],
                'available_departments' => [
                    ['id' => 'dept-1', 'label' => 'GSO - General Services Office'],
                ],
                'available_accountable_officers' => [
                    ['id' => 'officer-1', 'label' => 'Juan Dela Cruz'],
                ],
            ]);

        $this->app->instance(RpcppeReportServiceInterface::class, $service);

        $response = $this->get(route('gso.reports.rpcppe.print', ['preview' => 1]));

        $response->assertOk();
        $response->assertSee('Report on the Physical Count of Property, Plant and Equipment');
        $response->assertSee('PPE-001');
    }

    public function test_canonical_rpcsp_route_renders_the_new_report_view(): void
    {
        $this->withoutMiddleware();

        $service = Mockery::mock(RpcspReportServiceInterface::class);
        $service->shouldReceive('getPrintViewData')
            ->once()
            ->andReturn([
                'report' => [
                    'title' => 'Report on the Physical Count of Semi-Expendable Property',
                    'document' => [
                        'entity_name' => 'Municipality of San Francisco',
                        'appendix_label' => 'RPCSP',
                        'fund_source_id' => 'fund-1',
                        'department_id' => 'dept-1',
                        'accountable_officer_id' => 'officer-1',
                        'fund_source' => 'General Fund',
                        'fund_cluster' => 'FC-01 - General Fund Cluster',
                        'department' => 'GSO - General Services Office',
                        'accountable_officer' => 'Juan Dela Cruz',
                        'as_of' => '2026-03-21',
                        'as_of_label' => 'March 21, 2026',
                        'prefill_count' => false,
                        'summary' => [
                            'offices_covered' => 1,
                            'total_items' => 1,
                            'total_book_value' => 1500,
                            'total_balance_qty' => 3,
                        ],
                        'signatories' => [
                            'accountable_officer_name' => 'Juan Dela Cruz',
                            'accountable_officer_designation' => 'Supply Officer',
                            'committee_chair_name' => '',
                            'committee_member_1_name' => '',
                            'committee_member_2_name' => '',
                            'approved_by_name' => '',
                            'approved_by_designation' => '',
                            'verified_by_name' => '',
                            'verified_by_designation' => '',
                        ],
                    ],
                    'rows' => [[
                        'article' => 'Projector',
                        'description' => 'Office projector',
                        'property_no' => 'SE-010',
                        'unit' => 'unit',
                        'unit_value' => 1500,
                        'balance_per_card_qty' => 3,
                        'count_qty' => null,
                        'shortage_overage_qty' => null,
                        'shortage_overage_value' => null,
                        'remarks' => 'Office: ICT - ICT Office',
                    ]],
                    'pagination' => [
                        'pages' => [[
                            'rows' => [[
                                'article' => 'Projector',
                                'description' => 'Office projector',
                                'property_no' => 'SE-010',
                                'unit' => 'unit',
                                'unit_value' => 1500,
                                'balance_per_card_qty' => 3,
                                'count_qty' => null,
                                'shortage_overage_qty' => null,
                                'shortage_overage_value' => null,
                                'remarks' => 'Office: ICT - ICT Office',
                            ]],
                            'used_units' => 1,
                        ]],
                        'stats' => [
                            'page_count' => 1,
                            'rows_per_page' => 13,
                            'grid_rows' => 13,
                            'last_page_grid_rows' => 6,
                            'description_chars_per_line' => 44,
                            'page_row_counts' => [1],
                            'page_used_units' => [1],
                            'last_page_padding' => 5,
                        ],
                    ],
                ],
                'paperProfile' => [
                    'code' => 'a4-landscape',
                    'width' => '297mm',
                    'height' => '210mm',
                    'preview_width' => '297mm',
                    'header_image_web' => 'headers/a4_landscape_header_dark_3508x300.png',
                    'footer_image_web' => 'headers/a4_landscape_footer_dark_3508x250.png',
                    'header_image_pdf' => 'headers/a4_landscape_header_dark_3508x300.png',
                    'footer_image_pdf' => 'headers/a4_landscape_footer_dark_3508x250.png',
                    'pages_view' => 'gso::reports.rpcsp.print.paper.a4-landscape.pages',
                    'styles_view' => 'gso::reports.rpcsp.print.paper.a4-landscape.styles',
                    'pdf_styles_view' => 'gso::reports.rpcsp.print.paper.a4-landscape.pdf-styles',
                ],
                'available_funds' => [
                    ['id' => 'fund-1', 'label' => 'General Fund'],
                ],
                'available_departments' => [
                    ['id' => 'dept-1', 'label' => 'GSO - General Services Office'],
                ],
                'available_accountable_officers' => [
                    ['id' => 'officer-1', 'label' => 'Juan Dela Cruz'],
                ],
            ]);

        $this->app->instance(RpcspReportServiceInterface::class, $service);

        $response = $this->get(route('gso.reports.rpcsp.print', ['preview' => 1]));

        $response->assertOk();
        $response->assertSee('Report on the Physical Count of Semi-Expendable Property');
        $response->assertSee('SE-010');
    }

    public function test_legacy_rpci_route_redirects_to_canonical_gso_path(): void
    {
        $this->withoutMiddleware();

        $response = $this->get('/reports/rpci/print?preview=1&as_of=2026-03-27');

        $response->assertRedirect(route('gso.stocks.rpci.print', [
            'preview' => 1,
            'as_of' => '2026-03-27',
        ]));
    }

    public function test_canonical_rpci_route_renders_the_new_report_view(): void
    {
        $this->withoutMiddleware();

        $service = Mockery::mock(StockServiceInterface::class);
        $service->shouldReceive('getRpciPrintViewData')
            ->once()
            ->andReturn([
                'report' => [
                    'title' => 'Report on the Physical Count of Inventories',
                    'document' => [
                        'entity_name' => 'Municipality of San Francisco',
                        'appendix_label' => 'Annex 48',
                        'fund_source_id' => 'fund-1',
                        'accountable_officer_id' => 'officer-1',
                        'fund_source' => 'General Fund',
                        'fund_cluster' => 'FC-01 - General Fund Cluster',
                        'inventory_type' => 'Office Supplies',
                        'as_of' => '2026-03-27',
                        'as_of_label' => 'March 27, 2026',
                        'prefill_count' => true,
                        'summary' => [
                            'total_items' => 1,
                            'total_balance_qty' => 40,
                            'total_count_qty' => 40,
                            'total_shortage_overage_qty' => 0,
                            'total_book_value' => 10000,
                            'printed_rows' => 1,
                        ],
                        'signatories' => [
                            'accountable_officer_name' => 'Juan Dela Cruz',
                            'accountable_officer_designation' => 'Supply Officer',
                            'date_of_assumption' => '2026-01-15',
                            'committee_chair_name' => 'Ana Reyes',
                            'committee_member_1_name' => '',
                            'committee_member_2_name' => '',
                            'approved_by_name' => 'Maria Santos',
                            'approved_by_designation' => 'Municipal Mayor',
                            'verified_by_name' => 'Pedro Cruz',
                            'verified_by_designation' => 'Municipal Accountant',
                        ],
                    ],
                    'rows' => [[
                        'article' => 'Bond Paper',
                        'description' => 'A4 office paper',
                        'stock_no' => 'ICS-BOND-A4',
                        'unit' => 'ream',
                        'unit_value' => 250,
                        'balance_per_card_qty' => 40,
                        'count_qty' => 40,
                        'shortage_overage_qty' => 0,
                        'shortage_overage_value' => 0,
                        'remarks' => '',
                    ]],
                    'pagination' => [
                        'pages' => [[
                            'rows' => [[
                                'article' => 'Bond Paper',
                                'description' => 'A4 office paper',
                                'stock_no' => 'ICS-BOND-A4',
                                'unit' => 'ream',
                                'unit_value' => 250,
                                'balance_per_card_qty' => 40,
                                'count_qty' => 40,
                                'shortage_overage_qty' => 0,
                                'shortage_overage_value' => 0,
                                'remarks' => '',
                            ]],
                            'used_units' => 1,
                        ]],
                        'stats' => [
                            'page_count' => 1,
                            'rows_per_page' => 16,
                            'grid_rows' => 16,
                            'last_page_grid_rows' => 8,
                            'description_chars_per_line' => 44,
                            'page_row_counts' => [1],
                            'page_used_units' => [1],
                            'last_page_padding' => 7,
                        ],
                    ],
                ],
                'paperProfile' => [
                    'code' => 'a4-landscape',
                    'width' => '297mm',
                    'height' => '210mm',
                    'preview_width' => '297mm',
                    'rows_per_page' => 16,
                    'grid_rows' => 16,
                    'last_page_grid_rows' => 8,
                    'description_chars_per_line' => 44,
                    'pages_view' => 'gso::reports.rpci.print.paper.a4-landscape.pages',
                    'styles_view' => 'gso::reports.rpci.print.paper.a4-landscape.styles',
                    'pdf_styles_view' => 'gso::reports.rpci.print.paper.a4-landscape.pdf-styles',
                ],
                'available_funds' => [
                    ['id' => 'fund-1', 'label' => 'General Fund'],
                ],
            ]);

        $this->app->instance(StockServiceInterface::class, $service);

        $response = $this->get(route('gso.stocks.rpci.print', ['preview' => 1]));

        $response->assertOk();
        $response->assertSee('Report on the Physical Count of Inventories');
        $response->assertSee('ICS-BOND-A4');
        $response->assertSee('Office Supplies');
    }

    public function test_legacy_ssmi_route_redirects_to_canonical_gso_path(): void
    {
        $this->withoutMiddleware();

        $response = $this->get('/reports/ssmi/print?preview=1&date_from=2026-03-01&date_to=2026-03-31');

        $response->assertRedirect(route('gso.stocks.ssmi.print', [
            'preview' => 1,
            'date_from' => '2026-03-01',
            'date_to' => '2026-03-31',
        ]));
    }

    public function test_canonical_ssmi_route_renders_the_new_report_view(): void
    {
        $this->withoutMiddleware();

        $service = Mockery::mock(StockServiceInterface::class);
        $service->shouldReceive('getSsmiPrintViewData')
            ->once()
            ->andReturn([
                'report' => [
                    'title' => 'Summary of Supplies and Materials Issued',
                    'document' => [
                        'period_from' => '2026-03-01',
                        'period_to' => '2026-03-31',
                        'period_label' => 'March 1, 2026 to March 31, 2026',
                        'summary' => [
                            'total_ris' => 1,
                            'total_lines' => 1,
                            'total_qty' => 3,
                            'total_cost' => 750,
                        ],
                        'signatories' => [
                            'prepared_by_name' => 'Ana Reyes',
                            'prepared_by_designation' => 'GSO Designate',
                            'prepared_by_date' => '2026-03-31',
                            'certified_by_name' => 'Pedro Cruz',
                            'certified_by_designation' => 'Municipal Accountant',
                            'certified_by_date' => '2026-03-31',
                        ],
                    ],
                    'rows' => [[
                        'issue_date' => '03/21/2026',
                        'ris_number' => 'RIS-001',
                        'office' => 'GSO - General Services Office',
                        'stock_no' => 'ICS-BOND-A4',
                        'description' => 'Bond Paper A4',
                        'unit' => 'ream',
                        'qty_issued' => 3,
                        'unit_cost' => 250,
                        'total_cost' => 750,
                    ]],
                    'pagination' => [
                        'pages' => [[
                            'rows' => [[
                                'issue_date' => '03/21/2026',
                                'ris_number' => 'RIS-001',
                                'office' => 'GSO - General Services Office',
                                'stock_no' => 'ICS-BOND-A4',
                                'description' => 'Bond Paper A4',
                                'unit' => 'ream',
                                'qty_issued' => 3,
                                'unit_cost' => 250,
                                'total_cost' => 750,
                            ]],
                            'used_units' => 1,
                        ]],
                        'stats' => [
                            'page_count' => 1,
                            'page_used_units' => [1],
                            'last_page_padding' => 0,
                        ],
                    ],
                ],
                'paperProfile' => [
                    'code' => 'a4-landscape',
                    'pages_view' => 'gso::reports.ssmi.print.paper.a4-landscape.pages',
                    'styles_view' => 'gso::reports.ssmi.print.paper.a4-landscape.styles',
                    'pdf_styles_view' => 'gso::reports.ssmi.print.paper.a4-landscape.pdf-styles',
                    'width' => '297mm',
                    'height' => '210mm',
                ],
                'available_funds' => [],
            ]);

        $this->app->instance(StockServiceInterface::class, $service);

        $response = $this->get(route('gso.stocks.ssmi.print', ['preview' => 1]));

        $response->assertOk();
        $response->assertSee('Summary of Supplies and Materials Issued');
        $response->assertSee('RIS-001');
        $response->assertSee('ICS-BOND-A4');
    }

    public function test_legacy_stock_card_route_redirects_to_stocks_stock_card_mode(): void
    {
        $this->withoutMiddleware();

        $response = $this->get('/reports/stock-card?view=legacy&foo=bar');

        $response->assertRedirect(route('gso.stocks.index', [
            'view' => 'stock-cards',
            'foo' => 'bar',
        ]));
    }

    public function test_stock_card_mode_renders_the_stocks_page_with_stock_card_copy(): void
    {
        $this->withoutMiddleware();

        $response = $this->get(route('gso.stocks.index', ['view' => 'stock-cards']));

        $response->assertOk();
        $response->assertSee('Stock Card Source Items');
        $response->assertSee('open its Appendix 58 stock card preview');
    }

    public function test_batch_property_card_route_renders_cards_for_the_current_page(): void
    {
        $this->withoutMiddleware();

        $inventoryItem = new InventoryItem();
        $inventoryItem->id = 'inventory-1';

        $repository = Mockery::mock(InventoryItemRepositoryInterface::class);
        $repository->shouldReceive('paginateForTable')
            ->once()
            ->andReturn(new LengthAwarePaginator(
                collect([$inventoryItem]),
                1,
                15,
                1,
                ['path' => route('gso.inventory-items.property-cards.print-batch')]
            ));

        $printer = Mockery::mock(InventoryItemCardPrintServiceInterface::class);
        $printer->shouldReceive('getPropertyCardPrintPayload')
            ->once()
            ->andReturn([
                'view' => 'gso::property-cards.pc-print',
                'data' => [
                    'card' => [
                        'reference' => 'PROP-001',
                        'property_name' => 'Laptop Computer',
                        'fund' => 'General Fund',
                        'description' => 'Dell Latitude',
                        'starting_balance_qty' => 0,
                    ],
                    'entries' => [],
                    'maxGridRows' => 18,
                ],
            ]);

        $this->app->instance(InventoryItemRepositoryInterface::class, $repository);
        $this->app->instance(InventoryItemCardPrintServiceInterface::class, $printer);

        $response = $this->get(route('gso.inventory-items.property-cards.print-batch', ['preview' => 1]));

        $response->assertOk();
        $response->assertSee('Property Cards Batch Print');
        $response->assertSee('Laptop Computer');
        $response->assertSee('General Fund');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function reportPayload(string $appendixLabel, array $overrides = []): array
    {
        $payload = [
            'report' => [
                'entity_name' => 'Municipality of San Francisco',
                'appendix_label' => $appendixLabel,
                'fund_source_id' => '',
                'department_id' => '',
                'accountable_officer_id' => '',
                'fund_source' => 'General Fund',
                'fund_cluster' => 'FC-01 - General Fund Cluster',
                'department' => 'All Offices',
                'accountable_officer' => 'All Accountable Officers',
                'as_of' => '2026-03-21',
                'as_of_label' => 'March 21, 2026',
                'prefill_count' => false,
                'summary' => [
                    'offices_covered' => 1,
                    'total_items' => 1,
                    'total_book_value' => 1200,
                    'total_balance_qty' => 1,
                    'total_value' => 1200,
                ],
                'signatories' => [
                    'prepared_by_name' => 'Ana Reyes',
                    'prepared_by_designation' => 'GSO Focal Person',
                    'reviewed_by_name' => '',
                    'reviewed_by_designation' => '',
                    'approved_by_name' => '',
                    'approved_by_designation' => '',
                    'accountable_officer_name' => 'Juan Dela Cruz',
                    'accountable_officer_designation' => 'Supply Officer',
                    'committee_chair_name' => '',
                    'committee_member_1_name' => '',
                    'committee_member_2_name' => '',
                    'verified_by_name' => '',
                    'verified_by_designation' => '',
                ],
            ],
            'rows' => [],
            'available_funds' => [],
            'available_departments' => [],
            'available_accountable_officers' => [],
        ];

        return array_replace_recursive($payload, $overrides);
    }
}
