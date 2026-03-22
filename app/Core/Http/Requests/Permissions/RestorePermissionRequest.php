<?php

namespace App\Core\Http\Requests\Permissions;

use App\Core\Support\AdminContextAuthorizer;
use Illuminate\Foundation\Http\FormRequest;

class RestorePermissionRequest extends FormRequest
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
