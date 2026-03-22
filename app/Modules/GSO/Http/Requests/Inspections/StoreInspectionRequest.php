<?php

namespace App\Modules\GSO\Http\Requests\Inspections;

use App\Modules\GSO\Support\InspectionStatuses;
use App\Modules\GSO\Support\InventoryConditions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Administrator', 'admin'])
            || $this->user()?->can('modify Inspections');
    }

    protected function prepareForValidation(): void
    {
        $acquisitionCost = $this->input('acquisition_cost');

        if (is_string($acquisitionCost)) {
            $this->merge([
                'acquisition_cost' => str_replace(',', '', trim($acquisitionCost)),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'reviewer_user_id' => [
                'nullable',
                'uuid',
                Rule::exists('users', 'id')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
            'status' => ['nullable', Rule::in(InspectionStatuses::values())],
            'department_id' => [
                'nullable',
                'uuid',
                Rule::exists('departments', 'id')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
            'item_id' => [
                'nullable',
                'uuid',
                Rule::exists('items', 'id')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
            'office_department' => ['nullable', 'string', 'max:255'],
            'accountable_officer' => ['nullable', 'string', 'max:255'],
            'dv_number' => ['nullable', 'string', 'max:120'],
            'po_number' => ['nullable', 'string', 'max:120'],
            'observed_description' => ['nullable', 'string', 'max:2000'],
            'item_name' => ['nullable', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'acquisition_cost' => ['nullable', 'numeric', 'min:0'],
            'acquisition_date' => ['nullable', 'date'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'condition' => ['nullable', Rule::in(InventoryConditions::values())],
            'drive_folder_id' => ['nullable', 'string', 'max:120'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
