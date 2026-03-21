<?php

namespace App\Core\Http\Requests\Users;

use App\Http\Requests\BaseFormRequest;

class DeleteUserRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();
        return (bool) $this->user()?->hasRole('Administrator');
    }

    public function rules(): array
    {
        return []; // no body payload needed
    }
}
