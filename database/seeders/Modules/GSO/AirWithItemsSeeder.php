<?php

namespace Database\Seeders\Modules\GSO;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

use App\Core\Models\Department;
use App\Core\Models\Module;
use App\Core\Models\User;

use App\Modules\GSO\Models\Air;
use App\Modules\GSO\Models\AirItem;
use App\Modules\GSO\Models\Item;

class AirWithItemsSeeder extends Seeder
{
    public function run(): void
    {
        $gsoModule = Module::where('code', 'GSO')->firstOrFail();

        $gsoDepartment = Department::findOrFail(
            $gsoModule->default_department_id
        );

        $creator = User::query()
            ->where('is_active', true)
            ->firstOrFail();

        $accounting = Department::where('code', 'ACCOUNTING')->first();
        $mayor = Department::where('code', 'MAYOR')->first();
        $hr = Department::where('code', 'HRMO')->first();

        $scenarios = [
            [
                'department' => $accounting,
                'purpose' => 'Office supplies replenishment',
            ],
            [
                'department' => $mayor,
                'purpose' => 'Mayor office supplies',
            ],
            [
                'department' => $hr,
                'purpose' => 'HR office consumables',
            ],
        ];

        foreach ($scenarios as $scenario) {
            if (! $scenario['department']) {
                continue;
            }

            $air = Air::create([
                'id' => (string) Str::uuid(),
                'po_number' => 'PO-DRAFT-' . now()->format('Ymd') . '-' . str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'po_date' => now()->toDateString(),
                'air_date' => now()->toDateString(),
                'supplier_name' => 'TBD',
                'requesting_department_id' => $scenario['department']->id,
                'requesting_department_name_snapshot' => $scenario['department']->name,
                'requesting_department_code_snapshot' => $scenario['department']->code,
                'status' => 'draft',
                'created_by_user_id' => $creator->id,
                'created_by_name_snapshot' => $creator->username . ' (' . $creator->email . ')',
                'inspected_by_name' => 'TBD',
                'accepted_by_name' => 'TBD',
                'remarks' => $scenario['purpose'],
            ]);

            $items = Item::query()->inRandomOrder()->take(2)->get();

            foreach ($items as $item) {
                AirItem::create([
                    'id' => (string) Str::uuid(),
                    'air_id' => $air->id,
                    'item_id' => $item->id,
                    'stock_no_snapshot' => $item->item_identification,
                    'item_name_snapshot' => $item->item_name,
                    'description_snapshot' => $item->description,
                    'unit_snapshot' => $item->base_unit,
                    'acquisition_cost' => 1000,
                    'qty_ordered' => rand(1, 10),
                    'qty_delivered' => 0,
                    'qty_accepted' => 0,
                    'tracking_type_snapshot' => $item->tracking_type,
                    'requires_serial_snapshot' => $item->requires_serial,
                    'is_semi_expendable_snapshot' => $item->is_semi_expendable,
                ]);
            }
        }
    }
}