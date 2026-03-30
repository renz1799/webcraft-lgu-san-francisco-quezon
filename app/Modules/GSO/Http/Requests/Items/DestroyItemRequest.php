<?php

namespace App\Modules\GSO\Http\Requests\Items;

use App\Modules\GSO\Http\Requests\Concerns\AuthorizesGsoPermissions;
use Illuminate\Foundation\Http\FormRequest;

class DestroyItemRequest extends FormRequest
{
    use AuthorizesGsoPermissions;

    public function authorize(): bool
    {
        return $this->allowsGsoPermission('items.archive');
    }

    public function rules(): array
    {
        return [];
    }
}
