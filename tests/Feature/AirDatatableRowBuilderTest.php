<?php

namespace Tests\Feature;

use App\Modules\GSO\Builders\Air\AirDatatableRowBuilder;
use App\Modules\GSO\Models\Air;
use App\Modules\GSO\Support\Air\AirStatuses;
use Tests\TestCase;

class AirDatatableRowBuilderTest extends TestCase
{
    public function test_it_marks_inspected_air_with_pending_promotions(): void
    {
        $air = new Air([
            'id' => 'air-1',
            'po_number' => 'PO-2026-001',
            'status' => AirStatuses::INSPECTED,
        ]);
        $air->property_promotable_units_count = 2;
        $air->property_pending_units_count = 1;
        $air->consumable_promotable_lines_count = 1;
        $air->consumable_pending_lines_count = 1;

        $row = (new AirDatatableRowBuilder())->build($air);

        $this->assertSame(3, $row['promotion_eligible_count']);
        $this->assertSame(2, $row['promotion_pending_count']);
        $this->assertSame('pending', $row['promotion_status']);
        $this->assertSame('2 Pending', $row['promotion_status_text']);
    }

    public function test_it_marks_inspected_air_as_fully_promoted_when_nothing_is_pending(): void
    {
        $air = new Air([
            'id' => 'air-2',
            'po_number' => 'PO-2026-002',
            'status' => AirStatuses::INSPECTED,
        ]);
        $air->property_promotable_units_count = 1;
        $air->property_pending_units_count = 0;
        $air->consumable_promotable_lines_count = 1;
        $air->consumable_pending_lines_count = 0;

        $row = (new AirDatatableRowBuilder())->build($air);

        $this->assertSame('fully_promoted', $row['promotion_status']);
        $this->assertSame('Fully Promoted', $row['promotion_status_text']);
    }

    public function test_it_marks_non_inspected_air_as_not_eligible(): void
    {
        $air = new Air([
            'id' => 'air-3',
            'po_number' => 'PO-2026-003',
            'status' => AirStatuses::SUBMITTED,
        ]);
        $air->property_promotable_units_count = 4;
        $air->property_pending_units_count = 4;
        $air->consumable_promotable_lines_count = 0;
        $air->consumable_pending_lines_count = 0;

        $row = (new AirDatatableRowBuilder())->build($air);

        $this->assertSame('not_eligible', $row['promotion_status']);
        $this->assertSame('Not Eligible', $row['promotion_status_text']);
    }
}
