<?php

namespace App\Modules\GSO\Http\Requests\ICS\Items;

use Illuminate\Foundation\Http\FormRequest;

class AddIcsItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Administrator', 'admin'])
            || $this->user()?->hasRole('Staff')
            || $this->user()?->can('modify ICS');
    }

    public function rules(): array
    {
        return [
            'inventory_item_id' => ['required', 'uuid', 'exists:inventory_items,id'],
        ];
    }
}
