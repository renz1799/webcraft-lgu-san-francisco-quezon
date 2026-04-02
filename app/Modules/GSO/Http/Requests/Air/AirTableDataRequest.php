<?php

namespace App\Modules\GSO\Http\Requests\Air;

use App\Modules\GSO\Support\Air\AirStatuses;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AirTableDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsAnyPermission($user, [
            'air.view',
            'air.create',
            'air.update',
            'air.manage_items',
            'air.manage_files',
            'air.finalize_inspection',
            'air.reopen_inspection',
            'air.promote_inventory',
            'air.archive',
            'air.restore',
            'air.print',
        ]);
    }

    protected function prepareForValidation(): void
    {
        $rawStatus = trim((string) $this->query('status', ''));
        $workflowStatus = trim((string) $this->query('inspection_status', $rawStatus));
        $archived = trim((string) ($this->query('archived', $this->query('record_status', ''))));
        $sorters = $this->query('sorters', $this->query('sort', []));

        if (! in_array($workflowStatus, AirStatuses::values(), true)) {
            $workflowStatus = '';
        }

        if ($archived === '' && in_array($rawStatus, ['active', 'archived', 'all'], true)) {
            $archived = $rawStatus;
        }

        if ($archived === '') {
            $archived = 'active';
        }

        $this->merge([
            'search' => trim((string) ($this->query('search', $this->query('q', '')))),
            'archived' => $archived,
            'status' => $workflowStatus,
            'supplier' => trim((string) $this->query('supplier', '')),
            'department' => trim((string) $this->query('department', '')),
            'department_id' => trim((string) $this->query('department_id', '')),
            'fund_source_id' => trim((string) $this->query('fund_source_id', '')),
            'date_from' => trim((string) $this->query('date_from', '')),
            'date_to' => trim((string) $this->query('date_to', '')),
            'received_completeness' => trim((string) $this->query('received_completeness', '')),
            'sorters' => is_array($sorters) ? $sorters : [],
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
            'department' => ['nullable', 'string', 'max:255'],
            'department_id' => ['nullable', 'uuid'],
            'fund_source_id' => ['nullable', 'uuid'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'received_completeness' => ['nullable', Rule::in(['complete', 'partial'])],
            'sorters' => ['nullable', 'array'],
            'sorters.*.field' => ['nullable', 'string', 'max:100'],
            'sorters.*.dir' => ['nullable', 'in:asc,desc'],
            'page' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
