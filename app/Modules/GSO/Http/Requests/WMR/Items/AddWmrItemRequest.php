<?php

namespace App\Modules\GSO\Http\Requests\WMR\Items;

use Illuminate\Foundation\Http\FormRequest;

class AddWmrItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Administrator')
            || $this->user()?->hasRole('Staff')
            || $this->user()?->can('modify WMR');
    }

    public function rules(): array
    {
        return [
            'inventory_item_id' => ['required', 'uuid', 'exists:inventory_items,id'],
        ];
    }
}

