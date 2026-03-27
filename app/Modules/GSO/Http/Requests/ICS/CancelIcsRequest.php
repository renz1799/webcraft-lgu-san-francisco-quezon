<?php

namespace App\Modules\GSO\Http\Requests\ICS;

use Illuminate\Foundation\Http\FormRequest;

class CancelIcsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Administrator', 'admin'])
            || $this->user()?->hasRole('Staff')
            || $this->user()?->can('modify ICS');
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $reason = $this->input('reason');
        if (is_string($reason)) {
            $reason = trim($reason);
            $this->merge(['reason' => $reason === '' ? null : $reason]);
        }
    }
}
