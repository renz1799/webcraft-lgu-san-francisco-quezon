<?php

namespace App\Modules\GSO\Http\Requests\Items;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ItemTableDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Administrator', 'admin'])
            || $this->user()?->can('view Items')
            || $this->user()?->can('modify Items');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'search' => trim((string) ($this->query('search', $this->query('q', '')))),
            'archived' => trim((string) ($this->query('archived', $this->query('status', 'active')))),
            'asset_id' => trim((string) $this->query('asset_id', '')),
            'tracking_type' => trim((string) $this->query('tracking_type', '')),
            'requires_serial' => trim((string) $this->query('requires_serial', '')),
            'is_semi_expendable' => trim((string) $this->query('is_semi_expendable', '')),
            'page' => max(1, (int) $this->query('page', 1)),
            'size' => min(100, max(1, (int) $this->query('size', 15))),
        ]);
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'archived' => ['nullable', Rule::in(['active', 'archived', 'all'])],
            'asset_id' => ['nullable', 'uuid'],
            'tracking_type' => ['nullable', Rule::in(['property', 'consumable'])],
            'requires_serial' => ['nullable', Rule::in(['0', '1'])],
            'is_semi_expendable' => ['nullable', Rule::in(['0', '1'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
