<?php

namespace Tests\Feature;

use App\Modules\GSO\Services\Contracts\InventoryItemPublicAssetServiceInterface;
use Mockery;
use Tests\TestCase;

class GsoPublicAssetRouteTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_legacy_public_asset_route_redirects_to_canonical_gso_path(): void
    {
        $response = $this->get('/asset/PROP-001');

        $response->assertRedirect(route('gso.public-assets.show', ['code' => 'PROP-001']));
    }

    public function test_canonical_public_asset_route_renders_the_public_asset_page(): void
    {
        $service = Mockery::mock(InventoryItemPublicAssetServiceInterface::class);
        $service->shouldReceive('getPublicAssetPagePayload')
            ->once()
            ->with('PROP-001')
            ->andReturn([
                'view' => 'gso::inventory-items.public-show',
                'data' => [
                    'asset' => [
                        'type_label' => 'PPE',
                        'reference_label' => 'Property Number',
                        'reference_value' => 'PROP-001',
                        'description' => 'Laptop Computer',
                        'brand' => 'Dell',
                        'model' => 'Latitude',
                        'serial_number' => 'SN-001',
                        'acquisition_date' => 'March 01, 2026',
                        'office' => 'GSO - General Services Office',
                        'status' => 'Serviceable',
                        'condition' => 'Good',
                        'photos' => [],
                        'primary_photo_url' => null,
                    ],
                ],
            ]);

        $this->app->instance(InventoryItemPublicAssetServiceInterface::class, $service);

        $response = $this->get(route('gso.public-assets.show', ['code' => 'PROP-001']));

        $response->assertOk();
        $response->assertSee('Laptop Computer');
        $response->assertSee('PROP-001');
        $response->assertSee('General Services Office');
    }
}
