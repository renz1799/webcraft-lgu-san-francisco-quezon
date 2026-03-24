<?php

namespace Tests\Feature;

use App\Modules\GSO\Services\Contracts\Air\AirPrintServiceInterface;
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

    public function test_legacy_air_print_pdf_route_redirects_to_canonical_gso_pdf_path(): void
    {
        $this->withoutMiddleware();

        $airId = '11111111-1111-1111-1111-111111111111';

        $response = $this->get("/air/{$airId}/print/pdf?paper_profile=letter-portrait");

        $response->assertRedirect(route('gso.air.print.pdf', [
            'air' => $airId,
            'paper_profile' => 'letter-portrait',
        ]));
    }

    public function test_canonical_air_print_route_renders_the_platform_view(): void
    {
        $this->withoutMiddleware();

        $airId = '11111111-1111-1111-1111-111111111111';

        $service = Mockery::mock(AirPrintServiceInterface::class);
        $service->shouldReceive('buildReport')
            ->once()
            ->with($airId, null)
            ->andReturn([
                'report' => [
                    'title' => 'Acceptance and Inspection Report',
                    'air' => [
                        'id' => $airId,
                        'label' => 'PO-2026-001 / AIR-2026-0001',
                        'status' => 'inspected',
                        'status_text' => 'Inspected',
                        'continuation_label' => 'Root AIR',
                    ],
                    'document' => [
                        'appendix_label' => 'Appendix 30',
                        'supplier' => 'Acme Trading',
                        'fund_source' => 'GF - General Fund',
                        'office_department' => 'GSO - General Services Office',
                        'accepted_by_name' => 'Maria Clara',
                        'accepted_by_designation' => 'Supply Officer-Designate',
                        'inspected_by_name' => 'Juan Dela Cruz',
                        'inspected_by_designation' => 'Municipal Accountant',
                        'received_completeness' => 'complete',
                        'summary' => [
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
                    'max_grid_rows' => 24,
                    'can_open_inspection' => true,
                ],
                'paperProfile' => array_merge(
                    config('print.papers.a4-portrait', []),
                    config('printables.gso_air.profiles.a4-portrait', [])
                ),
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

    public function test_canonical_air_print_pdf_route_downloads_generated_pdf(): void
    {
        $this->withoutMiddleware();

        $airId = '11111111-1111-1111-1111-111111111111';
        $path = tempnam(sys_get_temp_dir(), 'air-print-');
        file_put_contents($path, 'pdf');

        $service = Mockery::mock(AirPrintServiceInterface::class);
        $service->shouldReceive('generatePdf')
            ->once()
            ->with($airId, 'letter-portrait')
            ->andReturn($path);

        $this->app->instance(AirPrintServiceInterface::class, $service);

        $response = $this->get(route('gso.air.print.pdf', [
            'air' => $airId,
            'paper_profile' => 'letter-portrait',
        ]));

        $response->assertOk();
        $response->assertDownload(basename($path));
        $response->assertHeader('content-type', 'application/pdf');
    }
}
