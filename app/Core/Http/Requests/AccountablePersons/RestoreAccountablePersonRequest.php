<?php

namespace App\Core\Http\Requests\AccountablePersons;

use App\Core\Http\Requests\AccountablePersons\Concerns\AuthorizesAccountablePersons;
use App\Http\Requests\BaseFormRequest;

class RestoreAccountablePersonRequest extends BaseFormRequest
{
    use AuthorizesAccountablePersons;

    public function authorize(): bool
    {
        return $this->canModifyAccountablePersons();
    }

    public function rules(): array
    {
        return [];
    }
}
