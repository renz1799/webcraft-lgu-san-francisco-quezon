<?php

namespace Database\Seeders\Modules\GSO;

use App\Core\Models\Department;
use App\Core\Models\Module;
use App\Core\Models\User;
use App\Modules\GSO\Models\Air;
use App\Modules\GSO\Models\AirItem;
use App\Modules\GSO\Models\AssetCategory;
use App\Modules\GSO\Models\FundSource;
use App\Modules\GSO\Models\Item;
use App\Modules\GSO\Models\Ris;
use App\Modules\GSO\Models\RisItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AirRisPrintTestSeeder extends Seeder
{
    private const ITEM_COUNT = 40;

    private const AIR_NUMBER = 'AIR-PRINT-TEST-2026-0001';

    private const PO_NUMBER = 'PO-PRINT-TEST-2026-0001';

    private const RIS_NUMBER = 'RIS-PRINT-TEST-2026-0001';

    public function run(): void
    {
        $creator = User::query()
            ->where('is_active', true)
            ->orderBy('created_at')
            ->firstOrFail();

        $department = $this->resolveDepartment();
        $fundSource = FundSource::query()->orderBy('name')->firstOrFail();
        $items = $this->ensurePrintItems(self::ITEM_COUNT);

        [$air, $ris] = DB::transaction(function () use ($creator, $department, $fundSource, $items): array {
            $air = $this->upsertAir($creator, $department, $fundSource);
            $this->refreshAirItems($air, $items);

            $ris = $this->upsertRis($air, $creator, $department, $fundSource);
            $this->refreshRisItems($ris, $items);

            return [$air->fresh('items'), $ris->fresh('items')];
        });

        $this->command?->info('AIR/RIS print test data ready.');
        $this->command?->line('AIR ID: ' . $air->id);
        $this->command?->line('AIR Number: ' . ($air->air_number ?? self::AIR_NUMBER));
        $this->command?->line('RIS ID: ' . $ris->id);
        $this->command?->line('RIS Number: ' . ($ris->ris_number ?? self::RIS_NUMBER));
        $this->command?->line('AIR Items: ' . $air->items->count());
        $this->command?->line('RIS Items: ' . $ris->items->count());
    }

    private function resolveDepartment(): Department
    {
        $gsoModule = Module::query()
            ->where('code', 'GSO')
            ->first();

        if ($gsoModule?->default_department_id) {
            $department = Department::query()->find($gsoModule->default_department_id);
            if ($department) {
                return $department;
            }
        }

        return Department::query()
            ->whereIn('code', ['GSO', 'ACCOUNTING', 'MAYOR'])
            ->orderBy('name')
            ->firstOrFail();
    }

    /**
     * @return Collection<int, Item>
     */
    private function ensurePrintItems(int $count): Collection
    {
        $assetCategory = AssetCategory::query()
            ->where('asset_code', 'ICS-01')
            ->firstOrFail();

        $units = ['ream', 'box', 'piece', 'pack', 'bottle'];

        return collect(range(1, $count))
            ->map(function (int $index) use ($assetCategory, $units): Item {
                $code = sprintf('PRINT-TEST-%03d', $index);
                $name = sprintf('Print Test Supply %02d', $index);
                $unit = $units[($index - 1) % count($units)];

                $item = Item::query()
                    ->withTrashed()
                    ->where('item_identification', $code)
                    ->first();

                if (! $item) {
                    $item = new Item([
                        'id' => (string) Str::uuid(),
                    ]);
                }

                if (method_exists($item, 'trashed') && $item->trashed()) {
                    $item->restore();
                }

                $item->fill([
                    'asset_id' => $assetCategory->id,
                    'item_name' => $name,
                    'description' => "Printable office supply {$index} used for AIR and RIS multi-page testing.",
                    'base_unit' => $unit,
                    'item_identification' => $code,
                    'major_sub_account_group' => 'Print Test Office Supplies',
                    'tracking_type' => 'consumable',
                    'requires_serial' => false,
                    'is_semi_expendable' => false,
                    'is_selected' => false,
                ]);
                $item->save();

                return $item;
            });
    }

    private function upsertAir(User $creator, Department $department, FundSource $fundSource): Air
    {
        $air = Air::query()
            ->withTrashed()
            ->where('air_number', self::AIR_NUMBER)
            ->first();

        if (! $air) {
            $air = new Air([
                'id' => (string) Str::uuid(),
            ]);
        }

        if ($air->trashed()) {
            $air->restore();
        }

        $air->fill([
            'po_number' => self::PO_NUMBER,
            'po_date' => now()->subDays(10)->toDateString(),
            'air_number' => self::AIR_NUMBER,
            'air_date' => now()->subDays(8)->toDateString(),
            'invoice_number' => 'INV-PRINT-TEST-2026-0001',
            'invoice_date' => now()->subDays(7)->toDateString(),
            'supplier_name' => 'Webcraft Print Test Trading',
            'requesting_department_id' => $department->id,
            'requesting_department_name_snapshot' => $department->name,
            'requesting_department_code_snapshot' => $department->code,
            'fund_source_id' => $fundSource->id,
            'fund' => $this->fundLabel($fundSource),
            'status' => 'inspected',
            'date_received' => now()->subDays(7)->toDateString(),
            'received_completeness' => 'complete',
            'received_notes' => 'Seeded multi-page AIR print sample for layout verification.',
            'date_inspected' => now()->subDays(6)->toDateString(),
            'inspection_verified' => true,
            'inspection_notes' => 'Inspection completed for seeded print verification data.',
            'inspected_by_name' => 'JUAN C. DELA CRUZ',
            'accepted_by_name' => 'MARIA L. SANTOS',
            'created_by_user_id' => $creator->id,
            'created_by_name_snapshot' => $creator->username . ' (' . $creator->email . ')',
            'remarks' => 'Seeded sample AIR with 40 line items for print testing.',
        ]);
        $air->save();

        return $air;
    }

    /**
     * @param  Collection<int, Item>  $items
     */
    private function refreshAirItems(Air $air, Collection $items): void
    {
        $air->items()->delete();

        foreach ($items->values() as $index => $item) {
            $quantity = (($index + 1) % 6) + 1;

            AirItem::query()->create([
                'id' => (string) Str::uuid(),
                'air_id' => $air->id,
                'item_id' => $item->id,
                'stock_no_snapshot' => $item->item_identification,
                'item_name_snapshot' => $item->item_name,
                'description_snapshot' => $item->description,
                'unit_snapshot' => $item->base_unit,
                'acquisition_cost' => 45 + (($index + 1) * 7.5),
                'qty_ordered' => $quantity,
                'qty_delivered' => $quantity,
                'qty_accepted' => $quantity,
                'tracking_type_snapshot' => $item->tracking_type,
                'requires_serial_snapshot' => (bool) $item->requires_serial,
                'is_semi_expendable_snapshot' => (bool) $item->is_semi_expendable,
                'remarks' => 'Print test AIR row ' . str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT),
            ]);
        }
    }

    private function upsertRis(Air $air, User $creator, Department $department, FundSource $fundSource): Ris
    {
        $ris = Ris::query()
            ->withTrashed()
            ->where('ris_number', self::RIS_NUMBER)
            ->first();

        if (! $ris) {
            $ris = new Ris([
                'id' => (string) Str::uuid(),
            ]);
        }

        if ($ris->trashed()) {
            $ris->restore();
        }

        $ris->fill([
            'air_id' => $air->id,
            'ris_number' => self::RIS_NUMBER,
            'ris_date' => now()->subDays(2)->toDateString(),
            'requesting_department_id' => $department->id,
            'requesting_department_name_snapshot' => $department->name,
            'requesting_department_code_snapshot' => $department->code,
            'fund' => $this->fundLabel($fundSource),
            'fund_source_id' => $fundSource->id,
            'fpp_code' => 'FPP-PRINT-2026',
            'division' => $department->name,
            'responsibility_center_code' => $department->code . '-RC-2026',
            'status' => 'issued',
            'submitted_by_name' => $creator->name ?: $creator->username,
            'submitted_at' => now()->subDays(2),
            'requested_by_name' => 'ELENA M. REYES',
            'requested_by_designation' => 'Administrative Officer IV',
            'requested_by_date' => now()->subDays(2)->toDateString(),
            'approved_by_name' => 'CARLOS P. GOMEZ',
            'approved_by_designation' => 'Municipal Mayor',
            'approved_by_date' => now()->subDay()->toDateString(),
            'issued_by_name' => 'ROBERT A. LIM',
            'issued_by_designation' => 'Supply Officer',
            'issued_by_date' => now()->toDateString(),
            'received_by_name' => 'ANA B. FLORES',
            'received_by_designation' => 'Administrative Aide VI',
            'received_by_date' => now()->toDateString(),
            'purpose' => 'Seeded multi-page RIS sample for A4 and Legal portrait print verification.',
            'remarks' => 'Seeded sample RIS with 40 line items for print testing.',
        ]);
        $ris->save();

        return $ris;
    }

    /**
     * @param  Collection<int, Item>  $items
     */
    private function refreshRisItems(Ris $ris, Collection $items): void
    {
        RisItem::query()
            ->withTrashed()
            ->where('ris_id', $ris->id)
            ->forceDelete();

        foreach ($items->values() as $index => $item) {
            $requested = (($index + 2) % 9) + 2;
            $issued = max(1, $requested - (($index + 1) % 3));

            RisItem::query()->create([
                'id' => (string) Str::uuid(),
                'ris_id' => $ris->id,
                'item_id' => $item->id,
                'line_no' => $index + 1,
                'stock_no_snapshot' => $item->item_identification,
                'description_snapshot' => $item->item_name . ' - ' . $item->description,
                'unit_snapshot' => $item->base_unit,
                'qty_requested' => $requested,
                'qty_issued' => $issued,
                'remarks' => (($index + 1) % 5) === 0 ? 'Priority release' : null,
            ]);
        }
    }

    private function fundLabel(FundSource $fundSource): string
    {
        $code = trim((string) ($fundSource->code ?? ''));
        $name = trim((string) ($fundSource->name ?? ''));

        if ($code !== '' && $name !== '') {
            return "{$code} - {$name}";
        }

        return $name !== '' ? $name : $code;
    }
}
