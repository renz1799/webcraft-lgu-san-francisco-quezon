<?php

namespace App\Core\Http\Requests\Auth;

use App\Http\Requests\BaseFormRequest;

class ResetPasswordRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['bail', 'required', 'string'],
            'email' => ['bail', 'required', 'email:rfc', 'max:255'],
            'password' => ['bail', 'required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
