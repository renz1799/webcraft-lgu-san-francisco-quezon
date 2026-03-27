<?php

namespace App\Modules\GSO\Http\Requests\PTR;

use Illuminate\Foundation\Http\FormRequest;

class DestroyPtrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Administrator')
            || $this->user()?->can('modify PTR');
    }

    public function rules(): array
    {
        return [];
    }
}
