<?php

namespace App\Modules\GSO\Http\Requests\Air;

use Illuminate\Foundation\Http\FormRequest;

class PrintAirRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('preview')) {
            $this->merge([
                'preview' => $this->boolean('preview'),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'preview' => ['nullable', 'boolean'],
        ];
    }
}
