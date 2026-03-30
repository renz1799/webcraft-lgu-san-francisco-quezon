<?php

namespace App\Modules\GSO\Http\Requests\InventoryItems;

use App\Modules\GSO\Http\Requests\Concerns\AuthorizesGsoPermissions;
use Illuminate\Foundation\Http\FormRequest;

class DestroyInventoryItemFileRequest extends FormRequest
{
    use AuthorizesGsoPermissions;

    public function authorize(): bool
    {
        return $this->allowsGsoPermission('inventory_items.manage_files');
    }

    public function rules(): array
    {
        return [];
    }
}
