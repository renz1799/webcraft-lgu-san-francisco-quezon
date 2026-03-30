<?php

namespace App\Modules\GSO\Http\Requests\RIS;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRisRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();

        return (bool) $u && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsPermission($u, 'ris.update');
    }

    public function rules(): array
    {
        return [
            'ris_number' => ['nullable', 'string', 'max:100'],

            // Required header fields
            'ris_date' => ['required', 'date'],
            'fund_source_id' => [
                'required',
                'uuid',
                Rule::exists('fund_sources', 'id')->where(function ($q) {
                    $q->whereNull('deleted_at')->where('is_active', true);
                }),
            ],
            'requesting_department_id' => [
                'required',
                'uuid',
                Rule::exists('departments', 'id')->where(function ($q) {
                    $q->whereNull('deleted_at');
                }),
            ],
            'purpose' => ['required', 'string'],

            // Optional header fields
            'fpp_code' => ['nullable', 'string', 'max:50'],
            'division' => ['nullable', 'string', 'max:150'],
            'responsibility_center_code' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string'],

            // Required signatories
            'requested_by_name' => ['required', 'string', 'max:150'],
            'requested_by_designation' => ['required', 'string', 'max:150'],
            'requested_by_date' => ['required', 'date'],

            'approved_by_name' => ['required', 'string', 'max:150'],
            'approved_by_designation' => ['required', 'string', 'max:150'],
            'approved_by_date' => ['required', 'date'],

            'issued_by_name' => ['required', 'string', 'max:150'],
            'issued_by_designation' => ['required', 'string', 'max:150'],
            'issued_by_date' => ['required', 'date'],

            'received_by_name' => ['required', 'string', 'max:150'],
            'received_by_designation' => ['required', 'string', 'max:150'],
            'received_by_date' => ['required', 'date'],
        ];
    }

    public function attributes(): array
    {
        return [
            'ris_date' => 'RIS date',
            'fund_source_id' => 'fund source',
            'requesting_department_id' => 'requesting department',
            'purpose' => 'purpose',

            'fpp_code' => 'FPP code',
            'division' => 'division',
            'responsibility_center_code' => 'responsibility center code',
            'remarks' => 'remarks',

            'requested_by_name' => 'requested by name',
            'requested_by_designation' => 'requested by designation',
            'requested_by_date' => 'requested by date',

            'approved_by_name' => 'approved by name',
            'approved_by_designation' => 'approved by designation',
            'approved_by_date' => 'approved by date',

            'issued_by_name' => 'issued by name',
            'issued_by_designation' => 'issued by designation',
            'issued_by_date' => 'issued by date',

            'received_by_name' => 'received by name',
            'received_by_designation' => 'received by designation',
            'received_by_date' => 'received by date',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'The :attribute field is required.',
            'fund_source_id.exists' => 'The selected fund source is invalid or inactive.',
            'requesting_department_id.exists' => 'The selected requesting department is invalid.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $trim = static function ($v) {
            if (!is_string($v)) return $v;
            $v = trim($v);
            return $v === '' ? null : $v;
        };

        $this->merge([
            'ris_number' => $trim($this->input('ris_number')),
            'ris_date' => $trim($this->input('ris_date')),
            'fund_source_id' => $trim($this->input('fund_source_id')),
            'requesting_department_id' => $trim($this->input('requesting_department_id')),

            'fpp_code' => $trim($this->input('fpp_code')),
            'division' => $trim($this->input('division')),
            'responsibility_center_code' => $trim($this->input('responsibility_center_code')),
            'purpose' => $trim($this->input('purpose')),
            'remarks' => $trim($this->input('remarks')),

            'requested_by_name' => $trim($this->input('requested_by_name')),
            'requested_by_designation' => $trim($this->input('requested_by_designation')),
            'requested_by_date' => $trim($this->input('requested_by_date')),

            'approved_by_name' => $trim($this->input('approved_by_name')),
            'approved_by_designation' => $trim($this->input('approved_by_designation')),
            'approved_by_date' => $trim($this->input('approved_by_date')),

            'issued_by_name' => $trim($this->input('issued_by_name')),
            'issued_by_designation' => $trim($this->input('issued_by_designation')),
            'issued_by_date' => $trim($this->input('issued_by_date')),

            'received_by_name' => $trim($this->input('received_by_name')),
            'received_by_designation' => $trim($this->input('received_by_designation')),
            'received_by_date' => $trim($this->input('received_by_date')),
        ]);
    }
}
