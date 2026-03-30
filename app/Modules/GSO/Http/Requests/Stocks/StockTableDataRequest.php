<?php

namespace App\Modules\GSO\Http\Requests\Stocks;

use App\Modules\GSO\Http\Requests\Concerns\AuthorizesGsoPermissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockTableDataRequest extends FormRequest
{
    use AuthorizesGsoPermissions;

    public function authorize(): bool
    {
        return $this->allowsAnyGsoPermission([
            'stocks.view',
            'stocks.adjust',
            'stocks.view_ledger',
        ]);
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'search' => trim((string) $this->query('search', '')),
            'archived' => trim((string) ($this->query('archived', $this->query('status', 'active')))),
            'fund_source_id' => trim((string) $this->query('fund_source_id', '')),
            'date_from' => trim((string) $this->query('date_from', '')),
            'date_to' => trim((string) $this->query('date_to', '')),
            'onhand_min' => trim((string) $this->query('onhand_min', '')),
            'onhand_max' => trim((string) $this->query('onhand_max', '')),
            'page' => max(1, (int) $this->query('page', 1)),
            'size' => min(100, max(1, (int) $this->query('size', 15))),
        ]);
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:255'],
            'archived' => ['nullable', Rule::in(['active', 'archived', 'all'])],
            'fund_source_id' => ['nullable', 'uuid'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'onhand_min' => ['nullable', 'integer', 'min:0'],
            'onhand_max' => ['nullable', 'integer', 'min:0'],
            'page' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
