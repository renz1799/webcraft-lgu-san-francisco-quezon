<?php
// app/Http/Requests/Users/ResetUserPasswordRequest.php

namespace App\Http\Requests\Users;

use App\Http\Requests\BaseFormRequest;

class ResetUserPasswordRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();
        // Allow admins or anyone with your chosen permission
        return $u && ($u->hasRole('admin') || $u->can('reset users') || $u->can('reset passwords'));
    }

    public function rules(): array
    {
        // No payload expected; endpoint just triggers a reset
        return [];
    }
}
