<?php

namespace App\Modules\GSO\Http\Requests\Air;

use App\Modules\GSO\Models\Air;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAirDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsPermission($user, 'air.update');
    }

    protected function prepareForValidation(): void
    {
        $fields = [
            'po_number',
            'air_number',
            'invoice_number',
            'supplier_name',
            'inspected_by_name',
            'accepted_by_name',
            'remarks',
            'requesting_department_id',
            'fund_source_id',
        ];

        $data = [];

        foreach ($fields as $field) {
            if ($this->has($field) && is_string($this->input($field))) {
                $data[$field] = trim($this->input($field));
            }
        }

        if ($data !== []) {
            $this->merge($data);
        }
    }

    public function rules(): array
    {
        $airId = trim((string) $this->route('air'));
        $air = $airId !== ''
            ? Air::query()->withTrashed()->select(['id', 'parent_air_id'])->find($airId)
            : null;
        $isFollowUp = $air?->parent_air_id !== null;

        $poNumberRules = ['required', 'string', 'max:255'];

        if (! $isFollowUp) {
            $poNumberRules[] = Rule::unique('airs', 'po_number')
                ->ignore($airId, 'id')
                ->where(fn ($query) => $query->whereNull('parent_air_id'));
        }

        return [
            'po_number' => $poNumberRules,
            'po_date' => ['required', 'date'],
            'air_number' => ['nullable', 'string', 'max:255'],
            'air_date' => ['required', 'date'],
            'invoice_number' => ['nullable', 'string', 'max:255'],
            'invoice_date' => ['nullable', 'date'],
            'supplier_name' => ['required', 'string', 'max:255'],
            'requesting_department_id' => [
                'required',
                'uuid',
                Rule::exists('departments', 'id')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
            'fund_source_id' => [
                'required',
                'uuid',
                Rule::exists('fund_sources', 'id')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
            'inspected_by_name' => ['required', 'string', 'max:255'],
            'accepted_by_name' => ['required', 'string', 'max:255'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function messages(): array
    {
        return [
            'po_number.required' => 'PO Number is required.',
            'po_number.unique' => 'PO Number already exists for another AIR record.',
            'po_date.required' => 'PO Date is required.',
            'air_date.required' => 'AIR Date is required.',
            'supplier_name.required' => 'Supplier Name is required.',
            'requesting_department_id.required' => 'Requesting Department is required.',
            'requesting_department_id.exists' => 'Selected requesting department is invalid.',
            'fund_source_id.required' => 'Fund Source is required.',
            'fund_source_id.exists' => 'Selected fund source is invalid.',
            'inspected_by_name.required' => 'Inspected By is required.',
            'accepted_by_name.required' => 'Accepted By is required.',
        ];
    }
}
