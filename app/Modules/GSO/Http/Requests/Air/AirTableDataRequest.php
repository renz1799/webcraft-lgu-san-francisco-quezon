<?php

namespace App\Modules\GSO\Http\Requests\Air;

use App\Modules\GSO\Support\AirStatuses;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AirTableDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Administrator', 'admin'])
            || $this->user()?->can('view AIR')
            || $this->user()?->can('modify AIR');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'search' => trim((string) ($this->query('search', $this->query('q', '')))),
            'archived' => trim((string) ($this->query('archived', $this->query('record_status', 'active')))),
            'status' => trim((string) $this->query('status', '')),
            'supplier' => trim((string) $this->query('supplier', '')),
            'department_id' => trim((string) $this->query('department_id', '')),
            'fund_source_id' => trim((string) $this->query('fund_source_id', '')),
            'date_from' => trim((string) $this->query('date_from', '')),
            'date_to' => trim((string) $this->query('date_to', '')),
            'page' => max(1, (int) $this->query('page', 1)),
            'size' => min(100, max(1, (int) $this->query('size', 15))),
        ]);
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'archived' => ['nullable', Rule::in(['active', 'archived', 'all'])],
            'status' => ['nullable', Rule::in(AirStatuses::values())],
            'supplier' => ['nullable', 'string', 'max:255'],
            'department_id' => ['nullable', 'uuid'],
            'fund_source_id' => ['nullable', 'uuid'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'page' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
