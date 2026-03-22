<?php

namespace App\Modules\GSO\Http\Requests\InventoryItems;

use App\Modules\GSO\Models\AccountableOfficer;
use App\Modules\GSO\Models\Item;
use App\Modules\GSO\Support\InventoryConditions;
use App\Modules\GSO\Support\InventoryCustodyStates;
use App\Modules\GSO\Support\InventoryStatuses;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Administrator', 'admin'])
            || $this->user()?->can('modify Inventory Items');
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('acquisition_cost')) {
            $raw = trim((string) $this->input('acquisition_cost'));
            $this->merge([
                'acquisition_cost' => $raw === '' ? null : str_replace(',', '', $raw),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'item_id' => [
                'required',
                'uuid',
                Rule::exists('items', 'id')->where(fn ($query) => $query
                    ->whereNull('deleted_at')
                    ->where('tracking_type', 'property')),
            ],
            'department_id' => [
                'required',
                'uuid',
                Rule::exists('departments', 'id')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
            'fund_source_id' => [
                'nullable',
                'uuid',
                Rule::exists('fund_sources', 'id')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
            'accountable_officer_id' => [
                'nullable',
                'uuid',
                Rule::exists('accountable_officers', 'id')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
            'property_number' => ['nullable', 'string', 'max:120'],
            'acquisition_date' => ['required', 'date'],
            'acquisition_cost' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:5000'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit' => ['required', 'string', 'max:50'],
            'stock_number' => ['nullable', 'string', 'max:120'],
            'service_life' => ['nullable', 'integer', 'min:0'],
            'is_ics' => ['nullable', 'boolean'],
            'accountable_officer' => ['nullable', 'string', 'max:255'],
            'custody_state' => ['required', Rule::in(InventoryCustodyStates::values())],
            'status' => ['required', Rule::in(InventoryStatuses::values())],
            'condition' => ['required', Rule::in(InventoryConditions::values())],
            'brand' => ['nullable', 'string', 'max:255'],
            'model' => ['nullable', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'po_number' => ['required', 'string', 'max:120'],
            'drive_folder_id' => ['nullable', 'string', 'max:120'],
            'remarks' => ['nullable', 'string', 'max:5000'],
            'air_item_unit_id' => ['nullable', 'uuid'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $itemId = trim((string) $this->input('item_id', ''));

            if ($itemId !== '') {
                $item = Item::query()
                    ->withTrashed()
                    ->select(['id', 'requires_serial', 'tracking_type', 'deleted_at'])
                    ->find($itemId);

                if (! $item || $item->trashed() || $item->tracking_type !== 'property') {
                    $validator->errors()->add('item_id', 'Selected item is invalid or is not property-tracked.');
                } elseif ((bool) $item->requires_serial && trim((string) $this->input('serial_number', '')) === '') {
                    $validator->errors()->add('serial_number', 'A serial number is required for the selected item.');
                }
            }

            $this->validateAccountableOfficer($validator);
        });
    }

    private function validateAccountableOfficer(mixed $validator): void
    {
        $accountableOfficerId = trim((string) $this->input('accountable_officer_id', ''));

        if ($accountableOfficerId === '') {
            return;
        }

        $accountableOfficer = AccountableOfficer::query()
            ->withTrashed()
            ->select(['id', 'deleted_at'])
            ->find($accountableOfficerId);

        if (! $accountableOfficer || $accountableOfficer->trashed()) {
            $validator->errors()->add('accountable_officer_id', 'Selected accountable officer is invalid.');
        }
    }
}
