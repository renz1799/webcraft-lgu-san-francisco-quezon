<?php

namespace App\Core\Http\Requests\Auth;

use App\Core\Support\AdminContextAuthorizer;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends BaseFormRequest
{

    //for public registration
    //public function authorize(): bool { return true; }

    public function authorize(): bool
    {
        return app(AdminContextAuthorizer::class)->canRegisterUsers($this->user());
    }


    public function rules(): array
    {
        return [
            'username' => ['bail', 'required', 'string', 'max:255', 'unique:users,username'],
            'email'    => ['bail', 'required', 'email:rfc', 'max:255', 'unique:users,email'],
            // UI sends role by NAME; scope to the proper guard
            'role'     => ['bail', 'required', 'string', Rule::exists('roles', 'name')->where('guard_name', 'web')],
            'password' => ['bail', 'required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
