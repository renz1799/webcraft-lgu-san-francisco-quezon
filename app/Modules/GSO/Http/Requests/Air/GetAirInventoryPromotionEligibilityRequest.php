<?php

namespace App\Modules\GSO\Http\Requests\Air;

use Illuminate\Foundation\Http\FormRequest;

class GetAirInventoryPromotionEligibilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Administrator', 'admin'])
            || $this->user()?->can('view AIR')
            || $this->user()?->can('modify AIR')
            || $this->user()?->can('modify Inventory Items');
    }

    public function rules(): array
    {
        return [];
    }
}
