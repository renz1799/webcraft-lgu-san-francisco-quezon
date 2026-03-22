<?php

namespace App\Modules\GSO\Http\Requests\InventoryItems;

use Illuminate\Foundation\Http\FormRequest;

class PrintRpcppeRequest extends FormRequest
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
            'prefill_count' => ['nullable', 'boolean'],
            'accountable_officer_name' => ['nullable', 'string', 'max:255'],
            'accountable_officer_designation' => ['nullable', 'string', 'max:255'],
            'committee_chair_name' => ['nullable', 'string', 'max:255'],
            'committee_member_1_name' => ['nullable', 'string', 'max:255'],
            'committee_member_2_name' => ['nullable', 'string', 'max:255'],
            'approved_by_name' => ['nullable', 'string', 'max:255'],
            'approved_by_designation' => ['nullable', 'string', 'max:255'],
            'verified_by_name' => ['nullable', 'string', 'max:255'],
            'verified_by_designation' => ['nullable', 'string', 'max:255'],
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
            'accountable_officer_name' => $this->nullableTrim('accountable_officer_name'),
            'accountable_officer_designation' => $this->nullableTrim('accountable_officer_designation'),
            'committee_chair_name' => $this->nullableTrim('committee_chair_name'),
            'committee_member_1_name' => $this->nullableTrim('committee_member_1_name'),
            'committee_member_2_name' => $this->nullableTrim('committee_member_2_name'),
            'approved_by_name' => $this->nullableTrim('approved_by_name'),
            'approved_by_designation' => $this->nullableTrim('approved_by_designation'),
            'verified_by_name' => $this->nullableTrim('verified_by_name'),
            'verified_by_designation' => $this->nullableTrim('verified_by_designation'),
            'prefill_count' => $this->boolean('prefill_count'),
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
