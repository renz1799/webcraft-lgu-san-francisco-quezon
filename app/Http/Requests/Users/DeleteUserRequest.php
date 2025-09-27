<?php

namespace App\Http\Requests\Users;

use App\Http\Requests\BaseFormRequest;

class DeleteUserRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();
        return $u && ($u->hasRole('admin') || $u->can('delete User Lists'));
    }

    public function rules(): array
    {
        return []; // no body payload needed
    }
}
