<?php

namespace App\Modules\GSO\Http\Requests\Departments;

use App\Http\Requests\BaseFormRequest;

class DestroyDepartmentRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user
            && ($user->hasAnyRole(['Administrator', 'admin']) || $user->can('modify Departments'));
    }

    public function rules(): array
    {
        return [];
    }
}
