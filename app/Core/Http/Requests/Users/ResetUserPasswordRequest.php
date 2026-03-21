<?php
// app/Http/Requests/Users/ResetUserPasswordRequest.php

namespace App\Core\Http\Requests\Users;

use App\Http\Requests\BaseFormRequest;

class ResetUserPasswordRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();
        // Allow Administrators or anyone with your chosen permission
        return (bool) $this->user()?->hasRole('Administrator');
    }

    public function rules(): array
    {
        // No payload expected; endpoint just triggers a reset
        return [];
    }
}
