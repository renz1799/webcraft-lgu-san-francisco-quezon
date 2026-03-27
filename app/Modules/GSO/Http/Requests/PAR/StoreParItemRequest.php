<?php

namespace App\Modules\GSO\Http\Requests\PAR;

use Illuminate\Foundation\Http\FormRequest;

class StoreParItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Administrator', 'admin'])
            || $this->user()?->hasRole('Staff')
            || $this->user()?->can('modify PAR');
    }

    public function rules(): array
    {
        return [
            'inventory_item_id' => ['required', 'uuid'],
            'quantity' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
