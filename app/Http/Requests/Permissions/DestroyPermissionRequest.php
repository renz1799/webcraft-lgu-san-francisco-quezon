<?php

namespace App\Http\Requests\Permissions;

use App\Http\Requests\BaseFormRequest;

class DestroyPermissionRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();

        return $u && $u->hasAnyRole(['Administrator', 'admin']);
    }

    public function rules(): array
    {
        return [];
    }
}
