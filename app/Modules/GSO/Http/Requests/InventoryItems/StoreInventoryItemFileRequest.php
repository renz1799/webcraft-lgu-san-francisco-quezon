<?php

namespace App\Modules\GSO\Http\Requests\InventoryItems;

use App\Modules\GSO\Support\InventoryFileTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryItemFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Administrator', 'admin'])
            || $this->user()?->can('modify Inventory Items');
    }

    public function rules(): array
    {
        return [
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['required', 'file', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,image/bmp,image/heic,application/pdf', 'max:10240'],
            'type' => ['nullable', Rule::in(InventoryFileTypes::values())],
        ];
    }
}
