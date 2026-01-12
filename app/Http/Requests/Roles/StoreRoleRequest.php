<?php

namespace App\Http\Requests\Roles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && ($user->hasRole('Administrator'));
    }

    public function rules(): array
    {
        return [
            'name'                  => ['required','string','max:255', Rule::unique('roles','name')],
            'permissions'           => ['array'],
            'permissions.*'         => ['uuid','exists:permissions,id'],
        ];
    }
}
