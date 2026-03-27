<?php

namespace App\Modules\GSO\Http\Requests\WMR;

use Illuminate\Foundation\Http\FormRequest;

class DestroyWmrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Administrator')
            || $this->user()?->can('modify WMR');
    }

    public function rules(): array
    {
        return [];
    }
}

