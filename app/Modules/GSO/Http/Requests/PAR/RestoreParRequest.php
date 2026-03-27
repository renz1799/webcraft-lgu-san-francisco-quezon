<?php

namespace App\Modules\GSO\Http\Requests\PAR;

use Illuminate\Foundation\Http\FormRequest;

class RestoreParRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Administrator', 'admin'])
            || $this->user()?->can('modify Allow Data Restoration')
            || $this->user()?->can('restore PAR');
    }

    public function rules(): array
    {
        return [];
    }
}
