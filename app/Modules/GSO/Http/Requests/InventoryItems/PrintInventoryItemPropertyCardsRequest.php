<?php

namespace App\Modules\GSO\Http\Requests\InventoryItems;

use App\Modules\GSO\Support\InventoryCustodyStates;
use App\Modules\GSO\Support\InventoryStatuses;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PrintInventoryItemPropertyCardsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'integer', 'min:1', 'max:50'],
            'search' => ['nullable', 'string', 'max:255'],
            'department_id' => ['nullable', 'uuid', 'exists:departments,id'],
            'item_id' => ['nullable', 'uuid', 'exists:items,id'],
            'fund_source_id' => ['nullable', 'uuid', 'exists:fund_sources,id'],
            'classification' => ['nullable', Rule::in(['ics', 'ppe'])],
            'custody_state' => ['nullable', Rule::in(InventoryCustodyStates::values())],
            'inventory_status' => ['nullable', Rule::in(InventoryStatuses::values())],
            'archived' => ['nullable', Rule::in(['active', 'archived', 'all'])],
            'preview' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $trimmed = [];
        foreach ([
            'search',
            'department_id',
            'item_id',
            'fund_source_id',
            'classification',
            'custody_state',
            'inventory_status',
            'archived',
        ] as $key) {
            if ($this->has($key) && is_string($this->input($key))) {
                $value = trim($this->input($key));
                $trimmed[$key] = $value !== '' ? $value : null;
            }
        }

        if ($this->has('preview')) {
            $trimmed['preview'] = $this->boolean('preview');
        }

        if ($trimmed !== []) {
            $this->merge($trimmed);
        }
    }
}
