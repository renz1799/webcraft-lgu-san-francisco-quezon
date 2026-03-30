<?php

namespace App\Modules\GSO\Http\Requests\InventoryItems;

use App\Modules\GSO\Http\Requests\Concerns\AuthorizesGsoPermissions;
use Illuminate\Foundation\Http\FormRequest;

class DestroyInventoryItemRequest extends FormRequest
{
    use AuthorizesGsoPermissions;

    public function authorize(): bool
    {
        return $this->allowsGsoPermission('inventory_items.archive');
    }

    public function rules(): array
    {
        return [];
    }
}
