<?php

namespace App\Http\Requests\Logs;

use Illuminate\Foundation\Http\FormRequest;

class LoginLogsDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && (
            $user->hasAnyRole(['Administrator', 'admin']) ||
            $user->can('view Login Logs')
        );
    }

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'integer', 'min:1', 'max:100'],

            'search' => ['nullable', 'string', 'max:200'],
            'q' => ['nullable', 'string', 'max:200'],
            'status' => ['nullable', 'in:success,failed'],
            'user' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'string', 'max:200'],
            'ip_address' => ['nullable', 'string', 'max:45'],
            'device' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],

            'sorters' => ['nullable', 'array'],
            'sorters.*.field' => ['nullable', 'string', 'max:100'],
            'sorters.*.dir' => ['nullable', 'in:asc,desc'],

            'filters' => ['nullable', 'array'],
            'filters.*.field' => ['nullable', 'string', 'max:100'],
            'filters.*.type' => ['nullable', 'string', 'max:50'],
            'filters.*.value' => ['nullable'],
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

        return $data;
    }
}
