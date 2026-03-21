<?php

namespace App\Core\Http\Requests\Permissions;

use Illuminate\Foundation\Http\FormRequest;

class AccessPermissionsDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();

        return $u && $u->hasAnyRole(['Administrator', 'admin']);
    }

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'integer', 'min:1', 'max:100'],

            'search' => ['nullable', 'string', 'max:200'],
            'q' => ['nullable', 'string', 'max:200'],
            'archived' => ['nullable', 'in:active,archived,all'],
            'name' => ['nullable', 'string', 'max:255'],
            'module' => ['nullable', 'string', 'max:255'],
            'guard_name' => ['nullable', 'in:web,api'],
            'role' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d'],

            'sorters' => ['nullable', 'array'],
            'sorters.*.field' => ['nullable', 'string', 'max:100'],
            'sorters.*.dir' => ['nullable', 'in:asc,desc'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $clean = [];

        foreach ($this->all() as $key => $value) {
            if (! is_string($value)) {
                $clean[$key] = $value;
                continue;
            }

            $value = trim($value);
            $clean[$key] = $value === '' ? null : $value;
        }

        $this->replace($clean);
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        $data['page'] = (int) ($data['page'] ?? 1);
        $data['size'] = (int) ($data['size'] ?? 15);
        $data['archived'] = $data['archived'] ?? 'active';

        return $data;
    }
}
