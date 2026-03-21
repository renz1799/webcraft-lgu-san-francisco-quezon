<?php

namespace App\Core\Http\Requests\Profile;

use App\Http\Requests\BaseFormRequest;

class UpdatePasswordRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required','current_password'],
            'new_password'     => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&#\._\-\+]/',
                'confirmed', // expects new_password_confirmation
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.current_password' => 'Current password is incorrect.',
        ];
    }
}
