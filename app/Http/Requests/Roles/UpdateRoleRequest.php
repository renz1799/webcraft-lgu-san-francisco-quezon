<?php

namespace App\Http\Requests\Roles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && ($user->hasRole('admin') || $user->can('modify User Permissions'));
    }

    public function rules(): array
    {
        $role = $this->route('role'); // implicit model binding

        return [
            'name'          => [
                'required','string','max:255',
                Rule::unique('roles','name')->ignore($role->id, 'id'),
            ],
            'permissions'   => ['array'],
            'permissions.*' => ['uuid','exists:permissions,id'],
        ];
    }
}
