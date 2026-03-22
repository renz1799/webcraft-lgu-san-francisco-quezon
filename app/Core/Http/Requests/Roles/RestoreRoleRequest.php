<?php

namespace App\Core\Http\Requests\Roles;

use App\Core\Support\AdminContextAuthorizer;
use Illuminate\Foundation\Http\FormRequest;

class RestoreRoleRequest extends FormRequest
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
