<?php

namespace App\Modules\GSO\Http\Requests\InventoryItems;

use Illuminate\Foundation\Http\FormRequest;

class PrintRegspiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fund_source_id' => ['nullable', 'uuid', 'exists:fund_sources,id'],
            'department_id' => ['nullable', 'uuid', 'exists:departments,id'],
            'accountable_officer_id' => ['nullable', 'uuid', 'exists:accountable_officers,id'],
            'as_of' => ['nullable', 'date'],
            'prepared_by_name' => ['nullable', 'string', 'max:255'],
            'prepared_by_designation' => ['nullable', 'string', 'max:255'],
            'reviewed_by_name' => ['nullable', 'string', 'max:255'],
            'reviewed_by_designation' => ['nullable', 'string', 'max:255'],
            'approved_by_name' => ['nullable', 'string', 'max:255'],
            'approved_by_designation' => ['nullable', 'string', 'max:255'],
            'preview' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'fund_source_id' => $this->nullableTrim('fund_source_id'),
            'department_id' => $this->nullableTrim('department_id'),
            'accountable_officer_id' => $this->nullableTrim('accountable_officer_id'),
            'as_of' => $this->nullableTrim('as_of'),
            'prepared_by_name' => $this->nullableTrim('prepared_by_name'),
            'prepared_by_designation' => $this->nullableTrim('prepared_by_designation'),
            'reviewed_by_name' => $this->nullableTrim('reviewed_by_name'),
            'reviewed_by_designation' => $this->nullableTrim('reviewed_by_designation'),
            'approved_by_name' => $this->nullableTrim('approved_by_name'),
            'approved_by_designation' => $this->nullableTrim('approved_by_designation'),
            'preview' => $this->has('preview') ? $this->boolean('preview') : null,
        ]);
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
