<?php

namespace App\Core\Http\Requests\AccountablePersons;

use App\Core\Http\Requests\AccountablePersons\Concerns\AuthorizesAccountablePersons;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateAccountablePersonRequest extends BaseFormRequest
{
    use AuthorizesAccountablePersons;

    public function authorize(): bool
    {
        return $this->canModifyAccountablePersons();
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'designation' => ['nullable', 'string', 'max:255'],
            'office' => ['nullable', 'string', 'max:255'],
            'department_id' => ['nullable', 'uuid', Rule::exists('departments', 'id')->whereNull('deleted_at')],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
