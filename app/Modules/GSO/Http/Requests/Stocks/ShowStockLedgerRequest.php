<?php

namespace App\Modules\GSO\Http\Requests\Stocks;

use App\Modules\GSO\Http\Requests\Concerns\AuthorizesGsoPermissions;
use App\Modules\GSO\Support\StockMovementTypes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShowStockLedgerRequest extends FormRequest
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
            'date_from' => $this->nullableTrim('date_from'),
            'date_to' => $this->nullableTrim('date_to'),
            'type' => $this->nullableTrim('type'),
            'fund_source_id' => $this->nullableTrim('fund_source_id'),
            'page' => max(1, (int) $this->query('page', 1)),
            'size' => min(100, max(1, (int) $this->query('size', 15))),
        ]);
    }

    public function rules(): array
    {
        return [
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'type' => ['nullable', Rule::in([...StockMovementTypes::values(), 'adjust'])],
            'fund_source_id' => ['nullable', 'uuid', 'exists:fund_sources,id'],
            'page' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    private function nullableTrim(string $key): ?string
    {
        $value = $this->input($key);

        if (! is_string($value)) {
            return $value;
        }

        $clean = trim($value);

        return $clean !== '' ? $clean : null;
    }
}
