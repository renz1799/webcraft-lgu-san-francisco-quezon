<?php

namespace App\Modules\GSO\Http\Requests\ITR;

use Illuminate\Foundation\Http\FormRequest;

class CancelItrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Administrator')
            || $this->user()?->hasRole('Staff')
            || $this->user()?->can('modify ITR');
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




