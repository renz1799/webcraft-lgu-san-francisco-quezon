<?php

namespace App\Modules\GSO\Http\Requests\PAR;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateParRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Administrator', 'admin'])
            || $this->user()?->hasRole('Staff')
            || $this->user()?->can('modify PAR');
    }

    public function rules(): array
    {
        return [
            'department_id' => [
                'required',
                'uuid',
                Rule::exists('departments', 'id')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
            'fund_source_id' => [
                'required',
                'uuid',
                Rule::exists('fund_sources', 'id')->where(fn ($query) => $query->whereNull('deleted_at')->where('is_active', true)),
            ],
            'person_accountable' => ['required', 'string', 'max:255'],
            'received_by_position' => ['required', 'string', 'max:120'],
            'received_by_date' => ['required', 'date'],
            'issued_by_name' => ['required', 'string', 'max:255'],
            'issued_by_position' => ['required', 'string', 'max:120'],
            'issued_by_office' => ['required', 'string', 'max:120'],
            'issued_by_date' => ['required', 'date'],
            'issued_date' => ['required', 'date'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'department_id' => 'Department',
            'fund_source_id' => 'Fund Source',
            'person_accountable' => 'Printed Name (End User)',
            'received_by_position' => 'Position / Office',
            'received_by_date' => 'Received By Date',
            'issued_by_name' => 'Issued By Printed Name',
            'issued_by_position' => 'Issued By Position',
            'issued_by_office' => 'Issued By Office',
            'issued_by_date' => 'Issued By Date',
            'issued_date' => 'Issued Date',
            'remarks' => 'Remarks',
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
            'department_id' => $trim($this->input('department_id')),
            'fund_source_id' => $trim($this->input('fund_source_id')),
            'person_accountable' => $trim($this->input('person_accountable')),
            'received_by_position' => $trim($this->input('received_by_position')),
            'received_by_date' => $trim($this->input('received_by_date')),
            'issued_by_name' => $trim($this->input('issued_by_name')),
            'issued_by_position' => $trim($this->input('issued_by_position')),
            'issued_by_office' => $trim($this->input('issued_by_office')),
            'issued_by_date' => $trim($this->input('issued_by_date')),
            'issued_date' => $trim($this->input('issued_date')),
            'remarks' => $trim($this->input('remarks')),
        ]);
    }
}
