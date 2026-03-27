<?php

namespace App\Modules\GSO\Http\Requests\PTR;

use Illuminate\Foundation\Http\FormRequest;

class FinalizePtrRequest extends FormRequest
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
