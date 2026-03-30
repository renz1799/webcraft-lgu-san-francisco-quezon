<?php

namespace App\Modules\GSO\Http\Requests\InventoryItems;

use App\Modules\GSO\Http\Requests\Concerns\AuthorizesGsoPermissions;
use Illuminate\Foundation\Http\FormRequest;

class RestoreInventoryItemRequest extends FormRequest
{
    use AuthorizesGsoPermissions;

    public function authorize(): bool
    {
        return $this->allowsGsoPermission('inventory_items.restore');
    }

    public function rules(): array
    {
        return [];
    }
}
