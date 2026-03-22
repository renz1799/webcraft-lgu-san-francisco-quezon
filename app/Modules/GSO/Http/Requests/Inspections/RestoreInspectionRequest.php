<?php

namespace App\Modules\GSO\Http\Requests\Inspections;

use Illuminate\Foundation\Http\FormRequest;

class RestoreInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Administrator', 'admin'])
            || $this->user()?->can('modify Inspections');
    }

    public function rules(): array
    {
        return [];
    }
}
