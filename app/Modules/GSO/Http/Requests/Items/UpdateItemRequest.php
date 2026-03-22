<?php

namespace App\Modules\GSO\Http\Requests\Items;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Administrator', 'admin'])
            || $this->user()?->can('modify Items');
    }

    public function rules(): array
    {
        return [
            'asset_id' => [
                'required',
                'uuid',
                Rule::exists('asset_categories', 'id')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
            'item_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'base_unit' => ['nullable', 'string', 'max:50'],
            'item_identification' => ['nullable', 'string', 'max:255'],
            'major_sub_account_group' => ['nullable', 'string', 'max:255'],
            'tracking_type' => ['required', Rule::in(['property', 'consumable'])],
            'requires_serial' => ['nullable', 'boolean'],
            'is_semi_expendable' => ['nullable', 'boolean'],
            'is_selected' => ['nullable', 'boolean'],
            'unit_conversions' => ['nullable', 'array'],
            'unit_conversions.*.from_unit' => ['required_with:unit_conversions', 'string', 'max:50'],
            'unit_conversions.*.multiplier' => ['required_with:unit_conversions', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $trackingType = (string) $this->input('tracking_type');
            $requiresSerial = (bool) $this->input('requires_serial');

            if ($trackingType === 'consumable' && $requiresSerial) {
                $validator->errors()->add('requires_serial', 'Consumable items cannot require serial numbers.');
            }

            $this->validateUnitConversions($validator);
        });
    }

    private function validateUnitConversions(mixed $validator): void
    {
        $baseUnit = strtolower(trim((string) $this->input('base_unit', '')));
        $conversions = $this->input('unit_conversions', []);

        if (! is_array($conversions)) {
            return;
        }

        $seen = [];

        foreach ($conversions as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $fromUnit = strtolower(trim((string) ($row['from_unit'] ?? '')));

            if ($fromUnit === '') {
                continue;
            }

            if (isset($seen[$fromUnit])) {
                $validator->errors()->add("unit_conversions.{$index}.from_unit", "Duplicate unit: {$fromUnit}.");
            }

            if ($baseUnit !== '' && $fromUnit === $baseUnit) {
                $validator->errors()->add("unit_conversions.{$index}.from_unit", "From unit must not be the same as base unit ({$baseUnit}).");
            }

            $seen[$fromUnit] = true;
        }
    }
}
