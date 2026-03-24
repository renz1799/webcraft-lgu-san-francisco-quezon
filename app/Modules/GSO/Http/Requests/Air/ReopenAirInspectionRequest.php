<?php

namespace App\Modules\GSO\Http\Requests\Air;

use Illuminate\Foundation\Http\FormRequest;

class ReopenAirInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Administrator', 'admin'])
            || $this->user()?->can('modify Inspection Status');
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
