<?php

namespace App\Modules\GSO\Http\Requests\PTR;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePtrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Administrator')
            || $this->user()?->hasRole('Staff')
            || $this->user()?->can('modify PTR');
    }

    public function rules(): array
    {
        return [
            'transfer_date' => ['nullable', 'date'],
            'from_department_id' => ['nullable', 'uuid', 'exists:departments,id'],
            'from_accountable_officer' => ['nullable', 'string', 'max:255'],
            'from_fund_source_id' => ['nullable', 'uuid', 'exists:fund_sources,id'],
            'to_department_id' => ['nullable', 'uuid', 'exists:departments,id'],
            'to_accountable_officer' => ['nullable', 'string', 'max:255'],
            'to_fund_source_id' => ['nullable', 'uuid', 'exists:fund_sources,id'],
            'transfer_type' => ['nullable', Rule::in(['donation', 'relocate', 'reassignment', 'others'])],
            'transfer_type_other' => ['nullable', 'string', 'max:255'],
            'reason_for_transfer' => ['nullable', 'string', 'max:5000'],
            'approved_by_name' => ['nullable', 'string', 'max:255'],
            'approved_by_designation' => ['nullable', 'string', 'max:255'],
            'approved_by_date' => ['nullable', 'date'],
            'released_by_name' => ['nullable', 'string', 'max:255'],
            'released_by_designation' => ['nullable', 'string', 'max:255'],
            'released_by_date' => ['nullable', 'date'],
            'received_by_name' => ['nullable', 'string', 'max:255'],
            'received_by_designation' => ['nullable', 'string', 'max:255'],
            'received_by_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $trim = static function ($value) {
            if (!is_string($value)) {
                return $value;
            }

            $value = trim($value);
            return $value === '' ? null : $value;
        };

        $this->merge([
            'transfer_date' => $trim($this->input('transfer_date')),
            'from_department_id' => $trim($this->input('from_department_id')),
            'from_accountable_officer' => $trim($this->input('from_accountable_officer')),
            'from_fund_source_id' => $trim($this->input('from_fund_source_id')),
            'to_department_id' => $trim($this->input('to_department_id')),
            'to_accountable_officer' => $trim($this->input('to_accountable_officer')),
            'to_fund_source_id' => $trim($this->input('to_fund_source_id')),
            'transfer_type' => $trim($this->input('transfer_type')),
            'transfer_type_other' => $trim($this->input('transfer_type_other')),
            'reason_for_transfer' => $trim($this->input('reason_for_transfer')),
            'approved_by_name' => $trim($this->input('approved_by_name')),
            'approved_by_designation' => $trim($this->input('approved_by_designation')),
            'approved_by_date' => $trim($this->input('approved_by_date')),
            'released_by_name' => $trim($this->input('released_by_name')),
            'released_by_designation' => $trim($this->input('released_by_designation')),
            'released_by_date' => $trim($this->input('released_by_date')),
            'received_by_name' => $trim($this->input('received_by_name')),
            'received_by_designation' => $trim($this->input('received_by_designation')),
            'received_by_date' => $trim($this->input('received_by_date')),
            'remarks' => $trim($this->input('remarks')),
        ]);
    }
}
