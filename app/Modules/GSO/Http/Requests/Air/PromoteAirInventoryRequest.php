<?php

namespace App\Modules\GSO\Http\Requests\Air;

use Illuminate\Foundation\Http\FormRequest;

class PromoteAirInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Administrator', 'admin'])
            || $this->user()?->can('modify AIR')
            || $this->user()?->can('modify Inventory Items');
    }

    public function rules(): array
    {
        return [
            'air_item_unit_ids' => ['sometimes', 'array'],
            'air_item_unit_ids.*' => ['uuid'],
        ];
    }
}
