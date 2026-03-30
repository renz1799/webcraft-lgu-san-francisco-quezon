<?php

namespace App\Modules\GSO\Http\Requests\Departments;

use App\Http\Requests\BaseFormRequest;
use App\Modules\GSO\Http\Requests\Concerns\AuthorizesGsoPermissions;

class RestoreDepartmentRequest extends BaseFormRequest
{
    use AuthorizesGsoPermissions;

    public function authorize(): bool
    {
        return $this->allowsGsoPermission('departments.restore');
    }

    public function rules(): array
    {
        return [];
    }
}
