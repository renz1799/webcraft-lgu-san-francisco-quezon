<?php

namespace App\Modules\GSO\Http\Requests\WMR;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWmrRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsPermission($user, 'wmr.update');
    }
    public function rules(): array
    {
        return [
            'fund_cluster_id' => ['nullable', 'uuid', 'exists:fund_clusters,id'],
            'place_of_storage' => ['nullable', 'string', 'max:255'],
            'report_date' => ['nullable', 'date'],
            'custodian_name' => ['nullable', 'string', 'max:255'],
            'custodian_designation' => ['nullable', 'string', 'max:255'],
            'custodian_date' => ['nullable', 'date'],
            'approved_by_name' => ['nullable', 'string', 'max:255'],
            'approved_by_designation' => ['nullable', 'string', 'max:255'],
            'approved_by_date' => ['nullable', 'date'],
            'inspection_officer_name' => ['nullable', 'string', 'max:255'],
            'inspection_officer_designation' => ['nullable', 'string', 'max:255'],
            'inspection_officer_date' => ['nullable', 'date'],
            'witness_name' => ['nullable', 'string', 'max:255'],
            'witness_designation' => ['nullable', 'string', 'max:255'],
            'witness_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $trim = static function ($value) {
            if (! is_string($value)) {
                return $value;
            }

            $value = trim($value);
            return $value === '' ? null : $value;
        };

        $this->merge([
            'fund_cluster_id' => $trim($this->input('fund_cluster_id')),
            'place_of_storage' => $trim($this->input('place_of_storage')),
            'report_date' => $trim($this->input('report_date')),
            'custodian_name' => $trim($this->input('custodian_name')),
            'custodian_designation' => $trim($this->input('custodian_designation')),
            'custodian_date' => $trim($this->input('custodian_date')),
            'approved_by_name' => $trim($this->input('approved_by_name')),
            'approved_by_designation' => $trim($this->input('approved_by_designation')),
            'approved_by_date' => $trim($this->input('approved_by_date')),
            'inspection_officer_name' => $trim($this->input('inspection_officer_name')),
            'inspection_officer_designation' => $trim($this->input('inspection_officer_designation')),
            'inspection_officer_date' => $trim($this->input('inspection_officer_date')),
            'witness_name' => $trim($this->input('witness_name')),
            'witness_designation' => $trim($this->input('witness_designation')),
            'witness_date' => $trim($this->input('witness_date')),
            'remarks' => $trim($this->input('remarks')),
        ]);
    }
}

