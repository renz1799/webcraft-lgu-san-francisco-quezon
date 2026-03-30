<?php

namespace App\Modules\GSO\Http\Requests\AccountableOfficers;

use App\Http\Requests\BaseFormRequest;
use App\Modules\GSO\Http\Requests\Concerns\AuthorizesGsoPermissions;

class DestroyAccountableOfficerRequest extends BaseFormRequest
{
    use AuthorizesGsoPermissions;

    public function authorize(): bool
    {
        return $this->allowsGsoPermission('accountable_persons.archive');
    }

    public function rules(): array
    {
        return [];
    }
}
