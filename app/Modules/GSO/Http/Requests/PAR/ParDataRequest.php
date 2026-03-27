<?php

namespace App\Modules\GSO\Http\Requests\PAR;

use Illuminate\Foundation\Http\FormRequest;

class ParDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Administrator', 'admin'])
            || $this->user()?->can('view PAR')
            || $this->user()?->can('modify PAR');
    }

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'integer', 'min:1', 'max:100'],

            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:50'],

            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],

            'department_id' => ['nullable', 'uuid'],

            // active | archived | all
            'record_status' => ['nullable', 'string', 'max:20'],
        ];
    }
}
