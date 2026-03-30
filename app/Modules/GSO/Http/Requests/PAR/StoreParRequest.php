<?php

namespace App\Modules\GSO\Http\Requests\PAR;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreParRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsPermission($user, 'par.create');
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

            // ============================
            // RECEIVED BY (End User)
            // ============================
            'person_accountable'    => ['required', 'string', 'max:255'],
            'received_by_position'  => ['required', 'string', 'max:120'],
            'received_by_date'      => ['required', 'date'],

            // ============================
            // ISSUED BY (Custodian)
            // ============================
            'issued_by_name'        => ['required', 'string', 'max:255'],
            'issued_by_position'    => ['required', 'string', 'max:120'],
            'issued_by_office'      => ['required', 'string', 'max:120'],
            'issued_by_date'        => ['required', 'date'],

            // Document date (can be set on finalize)
            'issued_date'           => ['required', 'date'],

            'remarks'               => ['nullable', 'string', 'max:2000'],
        ];
    }
}
