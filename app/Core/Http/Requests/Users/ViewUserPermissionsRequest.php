<?php

namespace App\Core\Http\Requests\Users;

use App\Core\Support\AdminContextAuthorizer;
use App\Http\Requests\BaseFormRequest;

class ViewUserPermissionsRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return app(AdminContextAuthorizer::class)->canManageCurrentContextAccess($this->user());
    }

    public function rules(): array
    {
        return []; // GET request, no payload validation needed
    }
}
