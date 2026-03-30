<?php

namespace App\Modules\GSO\Http\Requests\AccountableOfficers;

use App\Http\Requests\BaseFormRequest;
use App\Modules\GSO\Http\Requests\Concerns\AuthorizesGsoPermissions;

class RestoreAccountableOfficerRequest extends BaseFormRequest
{
    use AuthorizesGsoPermissions;

    public function authorize(): bool
    {
        return $this->allowsGsoPermission('accountable_persons.restore');
    }

    public function rules(): array
    {
        return [];
    }
}
