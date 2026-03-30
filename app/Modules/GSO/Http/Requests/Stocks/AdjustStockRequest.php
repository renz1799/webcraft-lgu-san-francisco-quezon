<?php

namespace App\Modules\GSO\Http\Requests\Stocks;

use App\Modules\GSO\Http\Requests\Concerns\AuthorizesGsoPermissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdjustStockRequest extends FormRequest
{
    use AuthorizesGsoPermissions;

    public function authorize(): bool
    {
        return $this->allowsGsoPermission('stocks.adjust');
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'item_id' => $this->nullableTrim('item_id'),
            'fund_source_id' => $this->nullableTrim('fund_source_id'),
            'type' => $this->nullableTrim('type'),
            'remarks' => $this->nullableTrim('remarks'),
            'qty' => (int) $this->input('qty'),
        ]);
    }

    public function rules(): array
    {
        return [
            'item_id' => ['required', 'uuid', 'exists:items,id'],
            'fund_source_id' => ['nullable', 'uuid', 'exists:fund_sources,id'],
            'type' => ['required', Rule::in(['increase', 'decrease', 'set'])],
            'qty' => ['required', 'integer', 'min:1'],
            'remarks' => ['nullable', 'string', 'max:255'],
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
