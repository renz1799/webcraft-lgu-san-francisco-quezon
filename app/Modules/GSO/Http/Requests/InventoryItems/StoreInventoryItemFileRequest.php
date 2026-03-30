<?php

namespace App\Modules\GSO\Http\Requests\InventoryItems;

use App\Modules\GSO\Http\Requests\Concerns\AuthorizesGsoPermissions;
use App\Modules\GSO\Support\InventoryFileTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryItemFileRequest extends FormRequest
{
    use AuthorizesGsoPermissions;

    public function authorize(): bool
    {
        return $this->allowsGsoPermission('inventory_items.manage_files');
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
