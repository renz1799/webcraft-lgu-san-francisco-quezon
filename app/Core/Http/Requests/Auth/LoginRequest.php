<?php

namespace App\Core\Http\Requests\Auth;

use App\Http\Requests\BaseFormRequest;

class LoginRequest extends BaseFormRequest
{

    public function authorize(): bool { return true; }

    
    public function rules(): array
    {
        return [
            'email'     => ['bail', 'required', 'email:rfc', 'max:255'],
            'password'  => ['bail', 'required', 'string', 'min:8'],
            'remember'  => ['sometimes', 'boolean'],
            'latitude'  => ['bail', 'required', 'numeric', 'between:-90,90'],
            'longitude' => ['bail', 'required', 'numeric', 'between:-180,180'],
        ];
    }
}
