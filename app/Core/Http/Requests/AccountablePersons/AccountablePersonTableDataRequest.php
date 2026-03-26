<?php

namespace App\Core\Http\Requests\AccountablePersons;

use App\Core\Http\Requests\AccountablePersons\Concerns\AuthorizesAccountablePersons;
use App\Http\Requests\BaseFormRequest;

class AccountablePersonTableDataRequest extends BaseFormRequest
{
    use AuthorizesAccountablePersons;

    public function authorize(): bool
    {
        return $this->canViewAccountablePersons();
    }

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'string', 'max:200'],
            'q' => ['nullable', 'string', 'max:200'],
            'archived' => ['nullable', 'in:active,archived,all'],
            'department_id' => ['nullable', 'uuid'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (! is_array($data)) {
            return $data;
        }

        $data['page'] = (int) ($data['page'] ?? 1);
        $data['size'] = (int) ($data['size'] ?? 15);
        $data['archived'] = $data['archived'] ?? 'active';
        $data['search'] = (string) ($data['search'] ?? $data['q'] ?? '');

        return $data;
    }
}
