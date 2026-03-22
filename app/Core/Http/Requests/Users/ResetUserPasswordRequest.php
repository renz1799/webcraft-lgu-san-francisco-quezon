<?php
// app/Http/Requests/Users/ResetUserPasswordRequest.php

namespace App\Core\Http\Requests\Users;

use App\Core\Support\AdminContextAuthorizer;
use App\Http\Requests\BaseFormRequest;

class ResetUserPasswordRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return app(AdminContextAuthorizer::class)->canManageCurrentContextAccess($this->user());
    }

    public function rules(): array
    {
        // No payload expected; endpoint just triggers a reset
        return [];
    }
}
