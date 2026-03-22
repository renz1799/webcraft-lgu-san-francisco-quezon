<?php

namespace Tests\Feature;

use App\Modules\GSO\Services\Contracts\AirPrintServiceInterface;
use Mockery;
use Tests\TestCase;

class GsoAirPrintRouteTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_legacy_air_print_route_redirects_to_canonical_gso_path(): void
    {
        $this->withoutMiddleware();

        $airId = '11111111-1111-1111-1111-111111111111';

        $response = $this->get("/air/{$airId}/print?preview=1");

        $response->assertRedirect(route('gso.air.print', [
            'air' => $airId,
            'preview' => 1,
        ]));
    }

    public function test_canonical_air_print_route_renders_the_platform_view(): void
    {
        $this->withoutMiddleware();

        $airId = '11111111-1111-1111-1111-111111111111';

        $service = Mockery::mock(AirPrintServiceInterface::class);
        $service->shouldReceive('getPrintViewData')
            ->once()
            ->with($airId)
            ->andReturn([
                'air' => [
                    'id' => $airId,
                    'label' => 'PO-2026-001 / AIR-2026-0001',
                    'status' => 'inspected',
                    'status_text' => 'Inspected',
                    'continuation_label' => 'Root AIR',
                ],
                'print' => [
                    'appendix_label' => 'Appendix 30',
                    'title' => 'Acceptance and Inspection Report',
                    'supplier' => 'Acme Trading',
                    'fund_source' => 'GF - General Fund',
                    'office_department' => 'GSO - General Services Office',
                    'accepted_by_name' => 'Maria Clara',
                    'inspected_by_name' => 'Juan Dela Cruz',
                    'summary' => [
                        'page_count' => 1,
                        'line_items' => 1,
                        'printed_rows' => 1,
                        'unit_rows' => 0,
                        'quantity_total' => 1,
                    ],
                ],
                'rows' => [[
                    'property_no' => 'ITM-001',
                    'description' => 'Laptop Computer',
                    'unit' => 'unit',
                    'quantity' => 1,
                ]],
                'pages' => [[[
                    'property_no' => 'ITM-001',
                    'description' => 'Laptop Computer',
                    'unit' => 'unit',
                    'quantity' => 1,
                ]]],
                'totalPages' => 1,
                'maxGridRows' => 24,
            ]);

        $this->app->instance(AirPrintServiceInterface::class, $service);

        $response = $this->get(route('gso.air.print', [
            'air' => $airId,
            'preview' => 1,
        ]));

        $response->assertOk();
        $response->assertSee('Acceptance and Inspection Report');
        $response->assertSee('Acme Trading');
        $response->assertSee('Laptop Computer');
        $response->assertSee('GF - General Fund');
    }
}
