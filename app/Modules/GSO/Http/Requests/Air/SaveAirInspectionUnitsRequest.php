<?php

namespace App\Modules\GSO\Http\Requests\Air;

use App\Modules\GSO\Support\InventoryConditions;
use App\Modules\GSO\Services\Air\AirInspectionWorkspaceAccessService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveAirInspectionUnitsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return app(AirInspectionWorkspaceAccessService::class)->canManage(
            $this->user(),
            (string) $this->route('air'),
        );
    }

    public function rules(): array
    {
        return [
            'units' => ['required', 'array'],
            'units.*.id' => ['nullable', 'uuid'],
            'units.*.brand' => ['nullable', 'string', 'max:255'],
            'units.*.model' => ['nullable', 'string', 'max:255'],
            'units.*.serial_number' => ['nullable', 'string', 'max:255'],
            'units.*.property_number' => ['nullable', 'string', 'max:255'],
            'units.*.condition_status' => ['nullable', Rule::in(InventoryConditions::values())],
            'units.*.condition_notes' => ['nullable', 'string', 'max:5000'],
            'units.*.inventory_item_id' => ['nullable', 'uuid', Rule::exists('inventory_items', 'id')],
            'units.*.components' => ['nullable', 'array'],
            'units.*.components.*.id' => ['nullable', 'uuid'],
            'units.*.components.*.name' => ['nullable', 'string', 'max:255'],
            'units.*.components.*.quantity' => ['nullable', 'integer', 'min:1'],
            'units.*.components.*.unit' => ['nullable', 'string', 'max:50'],
            'units.*.components.*.component_cost' => ['nullable', 'numeric'],
            'units.*.components.*.serial_number' => ['nullable', 'string', 'max:255'],
            'units.*.components.*.condition' => ['nullable', 'string', 'max:255'],
            'units.*.components.*.is_present' => ['nullable', 'boolean'],
            'units.*.components.*.remarks' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
