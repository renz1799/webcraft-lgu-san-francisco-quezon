<?php

namespace App\Modules\GSO\Http\Requests\Items;

use Illuminate\Foundation\Http\FormRequest;

class DestroyItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Administrator', 'admin'])
            || $this->user()?->can('modify Items');
    }

    public function rules(): array
    {
        return [];
    }
}
