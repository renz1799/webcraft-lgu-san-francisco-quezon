<?php

namespace App\Core\Http\Requests\Users;

use App\Core\Support\AdminContextAuthorizer;
use App\Http\Requests\BaseFormRequest;

class UpdateUserStatusRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return app(AdminContextAuthorizer::class)->canManageCurrentContextAccess($this->user());
    }

    public function rules(): array
    {
        return [
            'is_active' => ['required','boolean'],
        ];
    }
}
