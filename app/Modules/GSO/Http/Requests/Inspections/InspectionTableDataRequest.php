<?php

namespace App\Modules\GSO\Http\Requests\Inspections;

use App\Modules\GSO\Http\Requests\Concerns\AuthorizesGsoPermissions;
use App\Modules\GSO\Support\InspectionStatuses;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InspectionTableDataRequest extends FormRequest
{
    use AuthorizesGsoPermissions;

    public function authorize(): bool
    {
        return $this->allowsAnyGsoPermission([
            'inspections.view',
            'inspections.create',
            'inspections.update',
            'inspections.archive',
            'inspections.restore',
            'inspections.manage_photos',
        ]);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'search' => trim((string) $this->query('search', $this->query('q', ''))),
            'status' => trim((string) $this->query('status', '')),
            'department_id' => trim((string) $this->query('department_id', '')),
            'item_id' => trim((string) $this->query('item_id', '')),
            'archived' => trim((string) ($this->query('archived', $this->query('record_status', 'active')))),
            'page' => max(1, (int) $this->query('page', 1)),
            'size' => min(100, max(1, (int) $this->query('size', 15))),
        ]);
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', Rule::in(InspectionStatuses::values())],
            'department_id' => ['nullable', 'uuid'],
            'item_id' => ['nullable', 'uuid'],
            'archived' => ['nullable', Rule::in(['active', 'archived', 'all'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
