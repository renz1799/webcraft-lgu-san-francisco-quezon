<?php

namespace Tests\Feature;

use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Repositories\Contracts\InventoryItemRepositoryInterface;
use App\Modules\GSO\Services\Contracts\InventoryItemCardPrintServiceInterface;
use App\Modules\GSO\Services\Contracts\RegspiReportServiceInterface;
use App\Modules\GSO\Services\Contracts\RpcppeReportServiceInterface;
use App\Modules\GSO\Services\Contracts\RpcspReportServiceInterface;
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

        $response->assertRedirect(route('gso.inventory-items.regspi.print', [
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
            ->andReturn($this->reportPayload('RegSPI', [
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
                ]],
            ]));

        $this->app->instance(RegspiReportServiceInterface::class, $service);

        $response = $this->get(route('gso.inventory-items.regspi.print', ['preview' => 1]));

        $response->assertOk();
        $response->assertSee('Register of Semi-Expendable Property Issued');
        $response->assertSee('SE-001');
        $response->assertSee('Juan Dela Cruz');
    }

    public function test_canonical_rpcppe_route_renders_the_new_report_view(): void
    {
        $this->withoutMiddleware();

        $service = Mockery::mock(RpcppeReportServiceInterface::class);
        $service->shouldReceive('getPrintViewData')
            ->once()
            ->andReturn($this->reportPayload('RPCPPE', [
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
            ]));

        $this->app->instance(RpcppeReportServiceInterface::class, $service);

        $response = $this->get(route('gso.inventory-items.rpcppe.print', ['preview' => 1]));

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
            ->andReturn($this->reportPayload('RPCSP', [
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
            ]));

        $this->app->instance(RpcspReportServiceInterface::class, $service);

        $response = $this->get(route('gso.inventory-items.rpcsp.print', ['preview' => 1]));

        $response->assertOk();
        $response->assertSee('Report on the Physical Count of Semi-Expendable Property');
        $response->assertSee('SE-010');
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
