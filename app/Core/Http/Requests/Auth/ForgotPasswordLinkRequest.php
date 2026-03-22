<?php

namespace App\Core\Http\Requests\Auth;

use App\Http\Requests\BaseFormRequest;

class ForgotPasswordLinkRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['bail', 'required', 'email:rfc', 'max:255'],
        ];
    }
}
