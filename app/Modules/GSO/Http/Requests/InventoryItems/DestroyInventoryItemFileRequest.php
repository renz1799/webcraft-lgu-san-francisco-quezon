<?php

namespace App\Modules\GSO\Http\Requests\InventoryItems;

use Illuminate\Foundation\Http\FormRequest;

class DestroyInventoryItemFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Administrator', 'admin'])
            || $this->user()?->can('modify Inventory Items');
    }

    public function rules(): array
    {
        return [];
    }
}
