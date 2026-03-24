<?php

namespace App\Modules\GSO\Http\Requests\RIS;

use Illuminate\Foundation\Http\FormRequest;

class RisDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user();
    }

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'integer', 'min:1', 'max:100'],

            'search' => ['nullable', 'string', 'max:200'],
            'status' => ['nullable', 'string', 'max:30'], // workflow status (draft/submitted/...)
            'record_status' => ['nullable', 'string', 'max:30'], // active/archived/all

            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],

            'fund' => ['nullable', 'string', 'max:200'],
        ];
    }
}