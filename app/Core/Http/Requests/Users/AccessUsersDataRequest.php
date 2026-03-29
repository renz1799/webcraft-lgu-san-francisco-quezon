<?php

namespace App\Core\Http\Requests\Users;

use App\Core\Support\AdminContextAuthorizer;
use Illuminate\Foundation\Http\FormRequest;

class AccessUsersDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return app(AdminContextAuthorizer::class)->canManageCurrentContextAccess($this->user());
    }

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'integer', 'min:1', 'max:100'],

            'search' => ['nullable', 'string', 'max:200'],
            'q' => ['nullable', 'string', 'max:200'], // backward compatibility
            'archived' => ['nullable', 'in:active,archived,all'],
            'status' => ['nullable', 'in:active,inactive'],
            'platform_status' => ['nullable', 'in:active,inactive'],
            'role' => ['nullable', 'string', 'max:120'],
            'module_role' => ['nullable', 'string', 'max:120'],
            'module_access' => ['nullable', 'uuid'],
            'no_module_access' => ['nullable', 'boolean'],
            'multi_module_only' => ['nullable', 'boolean'],
            'username' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'string', 'max:200'],
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
        $booleanKeys = ['no_module_access', 'multi_module_only'];

        foreach ($this->all() as $key => $value) {
            if (in_array($key, $booleanKeys, true)) {
                if (is_string($value)) {
                    $value = strtolower(trim($value));

                    if ($value === '') {
                        $clean[$key] = null;
                        continue;
                    }

                    if (in_array($value, ['true', '1', 'yes', 'on'], true)) {
                        $clean[$key] = true;
                        continue;
                    }

                    if (in_array($value, ['false', '0', 'no', 'off'], true)) {
                        $clean[$key] = false;
                        continue;
                    }
                }

                if (is_bool($value) || is_int($value)) {
                    $clean[$key] = (bool) $value;
                    continue;
                }
            }

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
        $data['no_module_access'] = filter_var($data['no_module_access'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $data['multi_module_only'] = filter_var($data['multi_module_only'] ?? false, FILTER_VALIDATE_BOOLEAN);

        return $data;
    }
}
