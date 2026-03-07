<?php

namespace App\Http\Requests\Logs;

use Illuminate\Foundation\Http\FormRequest;

class LoginLogsDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // middleware handles access
    }

    public function rules(): array
    {
        return [
            // Tabulator remote pagination params
            'page' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'integer', 'min:1', 'max:100'],

            // Primary + advanced filters
            'search' => ['nullable', 'string', 'max:200'],
            'q' => ['nullable', 'string', 'max:200'], // backward compatibility
            'status' => ['nullable', 'in:success,failed'],
            'user' => ['nullable', 'string', 'max:150'],
            'email' => ['nullable', 'string', 'max:200'],
            'ip_address' => ['nullable', 'string', 'max:45'],
            'device' => ['nullable', 'string', 'max:255'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d'],

            // Tabulator remote sorting params: sorters[0][field], sorters[0][dir]
            'sorters' => ['nullable', 'array'],
            'sorters.*.field' => ['nullable', 'string', 'max:100'],
            'sorters.*.dir' => ['nullable', 'in:asc,desc'],

            // Tabulator filters support if needed later
            'filters' => ['nullable', 'array'],
            'filters.*.field' => ['nullable', 'string', 'max:100'],
            'filters.*.type' => ['nullable', 'string', 'max:50'],
            'filters.*.value' => ['nullable'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        // sensible defaults
        $data['page'] = (int)($data['page'] ?? 1);
        $data['size'] = (int)($data['size'] ?? 15);

        return $data;
    }
}
