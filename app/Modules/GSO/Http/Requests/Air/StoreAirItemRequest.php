<?php

namespace App\Modules\GSO\Http\Requests\Air;

use App\Modules\GSO\Http\Requests\Air\Concerns\ValidatesConfiguredAirItemUnits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAirItemRequest extends FormRequest
{
    use ValidatesConfiguredAirItemUnits;

    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Administrator', 'admin'])
            || $this->user()?->can('modify AIR');
    }

    public function rules(): array
    {
        return [
            'item_id' => ['required', 'uuid', 'exists:items,id'],
            'qty_ordered' => ['required', 'integer', 'min:1'],
            'unit_snapshot' => ['required', 'string', 'max:50'],
            'acquisition_cost' => ['required', 'numeric', 'min:0.01'],
            'description_snapshot' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function (Validator $validator): void {
            $item = $this->findItemForUnitValidation((string) $this->input('item_id', ''));

            $this->validateConfiguredUnitSelection(
                $validator,
                'unit_snapshot',
                $item,
                $this->input('unit_snapshot')
            );
        });
    }
}
