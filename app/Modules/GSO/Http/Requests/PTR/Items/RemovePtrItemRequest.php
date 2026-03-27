<?php

namespace App\Modules\GSO\Http\Requests\PTR\Items;

use Illuminate\Foundation\Http\FormRequest;

class RemovePtrItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Administrator')
            || $this->user()?->hasRole('Staff')
            || $this->user()?->can('modify PTR');
    }

    public function rules(): array
    {
        return [];
    }
}
