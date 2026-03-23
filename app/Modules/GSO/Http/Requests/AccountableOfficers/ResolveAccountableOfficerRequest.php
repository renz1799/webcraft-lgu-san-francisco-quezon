<?php

namespace App\Modules\GSO\Http\Requests\AccountableOfficers;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class ResolveAccountableOfficerRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        if ($user->hasAnyRole(['Administrator', 'admin'])) {
            return true;
        }

        foreach ([
            'modify Accountable Officers',
            'modify AIR',
            'modify RIS',
            'modify PAR',
            'modify ICS',
            'modify PTR',
            'modify ITR',
            'modify WMR',
        ] as $ability) {
            if ($user->can($ability)) {
                return true;
            }
        }

        return false;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'office' => ['nullable', 'string', 'max:255'],
            'department_id' => ['nullable', 'uuid', Rule::exists('departments', 'id')->whereNull('deleted_at')],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
