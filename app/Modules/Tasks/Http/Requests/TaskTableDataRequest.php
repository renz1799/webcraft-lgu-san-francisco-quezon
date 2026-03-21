<?php

namespace App\Modules\Tasks\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskTableDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        if (! $user->hasAnyRole(['Administrator', 'admin', 'Staff'])) {
            return false;
        }

        $scope = (string) ($this->input('scope', 'mine') ?? 'mine');

        if ($scope === 'all') {
            return $user->hasAnyRole(['Administrator', 'admin'])
                || $user->can('view All Tasks');
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'integer', 'min:1', 'max:100'],

            'search' => ['nullable', 'string', 'max:255'],
            'q' => ['nullable', 'string', 'max:255'],

            'archived' => ['nullable', 'in:active,archived,all'],
            'scope' => ['nullable', 'in:mine,available,all'],
            'status' => ['nullable', 'in:pending,in_progress,done,cancelled'],
            'assigned_to' => ['nullable', 'string', 'max:150'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],

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

        if (! is_array($data)) {
            return $data;
        }

        $data['page'] = (int) ($data['page'] ?? 1);
        $data['size'] = (int) ($data['size'] ?? 15);
        $data['archived'] = $data['archived'] ?? 'active';
        $data['scope'] = $data['scope'] ?? 'mine';

        if (! isset($data['search']) && isset($data['q'])) {
            $data['search'] = $data['q'];
        }

        return $data;
    }
}
