<?php

namespace App\Modules\GSO\Http\Requests\InventoryItems;

use App\Modules\GSO\Http\Requests\Concerns\AuthorizesGsoPermissions;
use App\Modules\GSO\Support\InventoryConditions;
use App\Modules\GSO\Support\InventoryEventTypes;
use App\Modules\GSO\Support\InventoryStatuses;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryItemEventRequest extends FormRequest
{
    use AuthorizesGsoPermissions;

    public function authorize(): bool
    {
        return $this->allowsGsoPermission('inventory_items.manage_events');
    }

    public function rules(): array
    {
        return [
            'event_type' => ['required', 'string', Rule::in(InventoryEventTypes::values())],
            'event_date' => ['required', 'date'],
            'quantity' => ['nullable', 'integer', 'min:0'],
            'department_id' => ['nullable', 'uuid', Rule::exists('departments', 'id')->where(fn ($query) => $query->whereNull('deleted_at'))],
            'person_accountable' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::in(InventoryStatuses::values())],
            'condition' => ['nullable', 'string', Rule::in(InventoryConditions::values())],
            'reference_type' => ['nullable', 'string', 'max:50'],
            'reference_no' => ['nullable', 'string', 'max:120'],
            'reference_id' => ['nullable', 'uuid'],
            'amount_snapshot' => ['nullable', 'numeric', 'min:0'],
            'unit_snapshot' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'fund_source_id' => ['nullable', 'uuid', Rule::exists('fund_sources', 'id')->where(fn ($query) => $query->whereNull('deleted_at'))],
        ];
    }
}
