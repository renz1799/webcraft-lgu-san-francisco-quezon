<?php

namespace App\Modules\GSO\Http\Requests\PAR;

use Illuminate\Foundation\Http\FormRequest;

class DestroyParRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Administrator', 'admin'])
            || $this->user()?->can('modify PAR');
    }

    public function rules(): array
    {
        return [];
    }
}
