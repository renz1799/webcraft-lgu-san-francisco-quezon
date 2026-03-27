<?php

namespace App\Modules\GSO\Http\Requests\WMR;

use Illuminate\Foundation\Http\FormRequest;

class CancelWmrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Administrator')
            || $this->user()?->hasRole('Staff')
            || $this->user()?->can('modify WMR');
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $reason = trim((string) $this->input('reason', ''));

        $this->merge([
            'reason' => $reason !== '' ? $reason : null,
        ]);
    }
}
