<?php

namespace App\Http\Requests\Permissions;

use Illuminate\Foundation\Http\FormRequest;

class RestorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && $user->hasAnyRole(['Administrator', 'admin']);
    }

    public function rules(): array
    {
        return [];
    }
}
