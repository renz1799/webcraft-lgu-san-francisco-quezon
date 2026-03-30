<?php

namespace App\Modules\GSO\Http\Requests\InventoryItems;

use App\Modules\GSO\Http\Requests\Concerns\AuthorizesGsoPermissions;
use App\Modules\GSO\Support\InventoryConditions;
use App\Modules\GSO\Support\InventoryCustodyStates;
use App\Modules\GSO\Support\InventoryStatuses;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InventoryItemTableDataRequest extends FormRequest
{
    use AuthorizesGsoPermissions;

    public function authorize(): bool
    {
        return $this->allowsAnyGsoPermission([
            'inventory_items.view',
            'inventory_items.create',
            'inventory_items.update',
            'inventory_items.archive',
            'inventory_items.restore',
            'inventory_items.manage_files',
            'inventory_items.manage_events',
            'inventory_items.import_from_inspection',
        ]);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'search' => trim((string) $this->query('search', '')),
            'archived' => trim((string) ($this->query('archived', $this->query('record_status', 'active')))),
            'department_id' => trim((string) $this->query('department_id', '')),
            'item_id' => trim((string) $this->query('item_id', '')),
            'fund_source_id' => trim((string) $this->query('fund_source_id', '')),
            'classification' => trim((string) $this->query('classification', '')),
            'custody_state' => trim((string) $this->query('custody_state', '')),
            'inventory_status' => trim((string) ($this->query('inventory_status', $this->query('status', '')))),
            'condition' => trim((string) $this->query('condition', '')),
            'page' => max(1, (int) $this->query('page', 1)),
            'size' => min(100, max(1, (int) $this->query('size', 15))),
        ]);
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'archived' => ['nullable', Rule::in(['active', 'archived', 'all'])],
            'department_id' => ['nullable', 'uuid'],
            'item_id' => ['nullable', 'uuid'],
            'fund_source_id' => ['nullable', 'uuid'],
            'classification' => ['nullable', Rule::in(['ics', 'ppe'])],
            'custody_state' => ['nullable', Rule::in(InventoryCustodyStates::values())],
            'inventory_status' => ['nullable', Rule::in(InventoryStatuses::values())],
            'condition' => ['nullable', Rule::in(InventoryConditions::values())],
            'page' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
