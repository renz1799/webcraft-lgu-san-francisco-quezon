<?php

namespace App\Core\Http\Requests\Permissions;

use App\Core\Support\AdminContextAuthorizer;
use App\Http\Requests\BaseFormRequest;

class DestroyPermissionRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return app(AdminContextAuthorizer::class)->canManageCurrentContextAccess($this->user());
    }

    public function rules(): array
    {
        return [];
    }
}
