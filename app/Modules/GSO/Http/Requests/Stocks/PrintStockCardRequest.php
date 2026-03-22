<?php

namespace App\Modules\GSO\Http\Requests\Stocks;

use Illuminate\Foundation\Http\FormRequest;

class PrintStockCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'fund_source_id' => $this->nullableTrim('fund_source_id'),
            'as_of' => $this->nullableTrim('as_of'),
            'preview' => $this->has('preview') ? $this->boolean('preview') : null,
        ]);
    }

    public function rules(): array
    {
        return [
            'fund_source_id' => ['nullable', 'uuid', 'exists:fund_sources,id'],
            'as_of' => ['nullable', 'date'],
            'preview' => ['nullable', 'boolean'],
        ];
    }

    private function nullableTrim(string $key): ?string
    {
        $value = $this->input($key);

        if (! is_string($value)) {
            return $value;
        }

        $clean = trim($value);

        return $clean !== '' ? $clean : null;
    }
}
