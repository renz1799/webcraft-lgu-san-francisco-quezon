<?php

namespace Tests\Feature;

use App\Core\Models\Department;
use App\Modules\GSO\Models\FundCluster;
use App\Modules\GSO\Models\FundSource;
use App\Modules\GSO\Models\InventoryItem;
use App\Modules\GSO\Models\InventoryItemEvent;
use App\Modules\GSO\Models\Item;
use App\Modules\GSO\Repositories\Contracts\InventoryItemEventRepositoryInterface;
use App\Modules\GSO\Services\InventoryItemCardPrintService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Mockery;
use Tests\TestCase;

class GsoInventoryItemPropertyCardServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_it_builds_ppe_property_card_payload_from_inventory_history(): void
    {
        $events = Mockery::mock(InventoryItemEventRepositoryInterface::class);
        $events->shouldReceive('getForPropertyCard')
            ->once()
            ->with('inventory-1')
            ->andReturn(new Collection([
                $this->makeEvent([
                    'event_type' => 'acquired',
                    'event_date' => Carbon::parse('2026-03-01 08:00:00'),
                    'qty_in' => 1,
                    'qty_out' => 0,
                    'amount_snapshot' => 55000.25,
                    'office_snapshot' => 'GSO - General Services Office',
                    'officer_snapshot' => 'Maria Clara',
                    'reference_type' => 'AIR',
                    'reference_no' => 'AIR-2026-001',
                    'notes' => 'Initial acquisition',
                ]),
                $this->makeEvent([
                    'event_type' => 'issued',
                    'event_date' => Carbon::parse('2026-03-15 10:30:00'),
                    'qty_in' => 0,
                    'qty_out' => 1,
                    'amount_snapshot' => 55000.25,
                    'office_snapshot' => 'MAYOR - Office of the Mayor',
                    'officer_snapshot' => 'Juan Dela Cruz',
                    'reference_type' => 'PAR',
                    'reference_no' => 'PAR-2026-002',
                    'notes' => 'Issued for office use',
                ]),
            ]));

        $service = new InventoryItemCardPrintService($events);
        $payload = $service->getPropertyCardPrintPayload($this->makeInventoryItem(false), [
            'preview' => true,
        ]);

        $this->assertSame('gso::property-cards.pc-print', $payload['view']);
        $this->assertSame('Laptop Computer', $payload['data']['card']['property_name']);
        $this->assertSame('PROP-001', $payload['data']['card']['reference']);
        $this->assertTrue($payload['data']['isPreview']);
        $this->assertCount(2, $payload['data']['entries']);
        $this->assertSame('AIR: AIR-2026-001', $payload['data']['entries'][0]['reference']);
        $this->assertSame(1, $payload['data']['entries'][0]['balance_qty']);
        $this->assertSame('PAR: PAR-2026-002', $payload['data']['entries'][1]['reference']);
        $this->assertSame(0, $payload['data']['entries'][1]['balance_qty']);
    }

    public function test_it_builds_ics_property_card_payload_with_receipt_and_issue_amounts(): void
    {
        $events = Mockery::mock(InventoryItemEventRepositoryInterface::class);
        $events->shouldReceive('getForPropertyCard')
            ->once()
            ->with('inventory-1')
            ->andReturn(new Collection([
                $this->makeEvent([
                    'event_type' => 'acquired',
                    'event_date' => Carbon::parse('2026-03-01 08:00:00'),
                    'qty_in' => 2,
                    'qty_out' => 0,
                    'amount_snapshot' => 1500.50,
                    'office_snapshot' => 'GSO - General Services Office',
                    'officer_snapshot' => 'Maria Clara',
                    'reference_type' => 'AIR',
                    'reference_no' => 'AIR-2026-010',
                    'notes' => 'Initial stock',
                ]),
                $this->makeEvent([
                    'event_type' => 'issued',
                    'event_date' => Carbon::parse('2026-03-21 14:00:00'),
                    'qty_in' => 0,
                    'qty_out' => 1,
                    'amount_snapshot' => 1500.50,
                    'office_snapshot' => 'ICT - ICT Office',
                    'officer_snapshot' => 'Pedro Reyes',
                    'reference_type' => 'ICS',
                    'reference_no' => 'ICS-2026-100',
                    'notes' => 'Released to end user',
                ]),
            ]));

        $service = new InventoryItemCardPrintService($events);
        $payload = $service->getPropertyCardPrintPayload($this->makeInventoryItem(true), [
            'preview' => true,
        ]);

        $this->assertSame('gso::property-cards.ics-print', $payload['view']);
        $this->assertSame('FC-01', $payload['data']['card']['fund_cluster']);
        $this->assertSame('PROP-001', $payload['data']['card']['se_property_number']);
        $this->assertCount(2, $payload['data']['entries']);
        $this->assertSame(1500.50, $payload['data']['entries'][0]['receipt_unit_cost']);
        $this->assertSame(3001.0, $payload['data']['entries'][0]['receipt_total_cost']);
        $this->assertSame('STK-001', $payload['data']['entries'][0]['item_no']);
        $this->assertSame(1500.50, $payload['data']['entries'][1]['issue_amount']);
        $this->assertSame(1, $payload['data']['entries'][1]['balance_qty']);
    }

    private function makeInventoryItem(bool $isIcs): InventoryItem
    {
        $item = new Item();
        $item->id = 'item-1';
        $item->item_name = 'Laptop Computer';

        $fundCluster = new FundCluster();
        $fundCluster->id = 'cluster-1';
        $fundCluster->code = 'FC-01';
        $fundCluster->name = 'General Fund Cluster';

        $fundSource = new FundSource();
        $fundSource->id = 'fund-1';
        $fundSource->name = 'General Fund';
        $fundSource->setRelation('fundCluster', $fundCluster);

        $department = new Department();
        $department->id = 'dept-1';
        $department->code = 'GSO';
        $department->name = 'General Services Office';

        $inventoryItem = new InventoryItem();
        $inventoryItem->id = 'inventory-1';
        $inventoryItem->item_id = 'item-1';
        $inventoryItem->is_ics = $isIcs;
        $inventoryItem->description = 'Dell Latitude Laptop';
        $inventoryItem->property_number = 'PROP-001';
        $inventoryItem->stock_number = 'STK-001';
        $inventoryItem->setRelation('item', $item);
        $inventoryItem->setRelation('fundSource', $fundSource);
        $inventoryItem->setRelation('department', $department);

        return $inventoryItem;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function makeEvent(array $attributes): InventoryItemEvent
    {
        $event = new InventoryItemEvent($attributes);

        if (! isset($attributes['office_snapshot'])) {
            $department = new Department();
            $department->code = 'GSO';
            $department->name = 'General Services Office';
            $event->setRelation('department', $department);
        }

        return $event;
    }
}
