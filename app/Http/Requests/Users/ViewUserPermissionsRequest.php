<?php

namespace App\Http\Requests\Users;

use App\Http\Requests\BaseFormRequest;

class ViewUserPermissionsRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();
        return (bool) $this->user()?->hasRole('Administrator');
    }

    public function rules(): array
    {
        return []; // GET request, no payload validation needed
    }
}
