<?php

namespace Tests\Feature;

use App\Core\Models\User;
use App\Core\Support\AdminContextAuthorizer;
use App\Modules\GSO\Http\Requests\AccountableOfficers\ResolveAccountableOfficerRequest;
use App\Modules\GSO\Http\Requests\Air\PrintAirRequest;
use App\Modules\GSO\Http\Requests\AssetTypes\AssetTypeTableDataRequest;
use App\Modules\GSO\Http\Requests\Inspections\UpdateInspectionRequest;
use App\Modules\GSO\Http\Requests\InventoryItems\UpdateInventoryItemRequest;
use App\Modules\GSO\Http\Requests\Items\StoreItemRequest;
use App\Modules\GSO\Http\Requests\Reports\PrintPropertyCardsRequest;
use App\Modules\GSO\Http\Requests\RIS\RestoreRisRequest;
use App\Modules\GSO\Http\Requests\RIS\Workflow\SubmitRisRequest;
use App\Modules\GSO\Http\Requests\Stocks\StockTableDataRequest;
use Mockery;
use Tests\TestCase;

class GsoRequestAuthorizationTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_store_item_request_uses_items_create_permission(): void
    {
        $request = StoreItemRequest::create('/gso/inventory/items', 'POST');
        $request->setContainer($this->app);
        $request->setUserResolver(fn () => $this->makeUser('user-1'));

        $this->mockAuthorizerForPermission('items.create', true);

        $this->assertTrue($request->authorize());
    }

    public function test_asset_type_table_request_uses_permission_first_access_list(): void
    {
        $request = AssetTypeTableDataRequest::create('/gso/reference-data/asset-types/data', 'GET');
        $request->setContainer($this->app);
        $request->setUserResolver(fn () => $this->makeUser('user-1'));

        $this->mockAuthorizerForAnyPermission(function ($user, array $permissions): bool {
            return $user instanceof User
                && $permissions === [
                    'asset_types.view',
                    'asset_types.create',
                    'asset_types.update',
                    'asset_types.archive',
                    'asset_types.restore',
                ];
        });

        $this->assertTrue($request->authorize());
    }

    public function test_update_inventory_item_request_uses_update_permission_for_patch_requests(): void
    {
        $request = UpdateInventoryItemRequest::create('/gso/inventory/inventory-items/item-1', 'PATCH');
        $request->setContainer($this->app);
        $request->setUserResolver(fn () => $this->makeUser('user-1'));

        $this->mockAuthorizerForPermission('inventory_items.update', true);

        $this->assertTrue($request->authorize());
    }

    public function test_update_inspection_request_uses_update_permission_for_patch_requests(): void
    {
        $request = UpdateInspectionRequest::create('/gso/inventory/inspections/inspection-1', 'PATCH');
        $request->setContainer($this->app);
        $request->setUserResolver(fn () => $this->makeUser('user-1'));

        $this->mockAuthorizerForPermission('inspections.update', true);

        $this->assertTrue($request->authorize());
    }

    public function test_stock_table_request_uses_permission_first_access_list(): void
    {
        $request = StockTableDataRequest::create('/gso/stocks/data', 'GET');
        $request->setContainer($this->app);
        $request->setUserResolver(fn () => $this->makeUser('user-1'));

        $this->mockAuthorizerForAnyPermission(function ($user, array $permissions): bool {
            return $user instanceof User
                && $permissions === [
                    'stocks.view',
                    'stocks.adjust',
                    'stocks.view_ledger',
                ];
        });

        $this->assertTrue($request->authorize());
    }

    public function test_accountable_officer_resolver_uses_normalized_document_permissions_context_aware(): void
    {
        $request = ResolveAccountableOfficerRequest::create('/gso/reference-data/accountable-officers/resolve', 'POST');
        $request->setContainer($this->app);
        $request->setUserResolver(fn () => $this->makeUser('user-1'));

        $this->mockAuthorizerForAnyPermission(function ($user, array $permissions): bool {
            return $user instanceof User
                && $permissions === [
                    'accountable_persons.create',
                    'accountable_persons.update',
                    'air.update',
                    'ris.update',
                    'par.update',
                    'ics.update',
                    'ptr.update',
                    'itr.update',
                    'wmr.update',
                ];
        });

        $this->assertTrue($request->authorize());
    }

    public function test_air_print_request_uses_permission_first_access_list(): void
    {
        $request = PrintAirRequest::create('/gso/airs/air-1/print', 'GET');
        $request->setContainer($this->app);
        $request->setUserResolver(fn () => $this->makeUser('user-1'));

        $this->mockAuthorizerForAnyPermission(function ($user, array $permissions): bool {
            return $user instanceof User
                && $permissions === [
                    'air.print',
                    'air.view',
                    'air.update',
                ];
        });

        $this->assertTrue($request->authorize());
    }

    public function test_submit_ris_request_uses_submit_or_update_permissions(): void
    {
        $request = SubmitRisRequest::create('/gso/ris/ris-1/submit', 'POST');
        $request->setContainer($this->app);
        $request->setUserResolver(fn () => $this->makeUser('user-1'));

        $this->mockAuthorizerForAnyPermission(function ($user, array $permissions): bool {
            return $user instanceof User
                && $permissions === [
                    'ris.submit',
                    'ris.update',
                ];
        });

        $this->assertTrue($request->authorize());
    }

    public function test_restore_ris_request_uses_restore_or_audit_restore_permissions(): void
    {
        $request = RestoreRisRequest::create('/gso/ris/ris-1/restore', 'POST');
        $request->setContainer($this->app);
        $request->setUserResolver(fn () => $this->makeUser('user-1'));

        $this->mockAuthorizerForAnyPermission(function ($user, array $permissions): bool {
            return $user instanceof User
                && $permissions === [
                    'ris.restore',
                    'audit_logs.restore_data',
                ];
        });

        $this->assertTrue($request->authorize());
    }

    public function test_property_cards_print_request_uses_report_or_inventory_permissions(): void
    {
        $request = PrintPropertyCardsRequest::create('/gso/reports/property-cards/print', 'GET');
        $request->setContainer($this->app);
        $request->setUserResolver(fn () => $this->makeUser('user-1'));

        $this->mockAuthorizerForAnyPermission(function ($user, array $permissions): bool {
            return $user instanceof User
                && $permissions === [
                    'reports.property_cards.view',
                    'inventory_items.view',
                    'inventory_items.update',
                ];
        });

        $this->assertTrue($request->authorize());
    }

    private function makeUser(string $id): User
    {
        $user = new User();
        $user->forceFill([
            'id' => $id,
            'username' => $id,
            'email' => "{$id}@example.com",
        ]);

        return $user;
    }

    private function mockAuthorizerForPermission(string $permission, bool $result): void
    {
        $authorizer = Mockery::mock(AdminContextAuthorizer::class);
        $authorizer->shouldReceive('allowsPermission')
            ->withArgs(function ($user, string $requestedPermission) use ($permission): bool {
                return $user instanceof User && $requestedPermission === $permission;
            })
            ->andReturn($result);

        app()->instance(AdminContextAuthorizer::class, $authorizer);
    }

    private function mockAuthorizerForAnyPermission(callable $matcher): void
    {
        $authorizer = Mockery::mock(AdminContextAuthorizer::class);
        $authorizer->shouldReceive('allowsAnyPermission')
            ->withArgs(function ($user, array|string $permissions) use ($matcher): bool {
                return is_array($permissions) && $matcher($user, $permissions);
            })
            ->andReturn(true);

        app()->instance(AdminContextAuthorizer::class, $authorizer);
    }
}
