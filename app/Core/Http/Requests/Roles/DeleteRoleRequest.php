<?php

namespace App\Core\Http\Requests\Roles;

use Illuminate\Foundation\Http\FormRequest;

class DeleteRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && ($user->hasRole('Administrator'));
    }

    public function rules(): array
    {
        return [];
    }
}
