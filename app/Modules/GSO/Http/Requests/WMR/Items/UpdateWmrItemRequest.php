<?php

namespace App\Modules\GSO\Http\Requests\WMR\Items;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWmrItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Administrator')
            || $this->user()?->hasRole('Staff')
            || $this->user()?->can('modify WMR');
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1'],
            'disposal_method' => ['nullable', Rule::in(['destroyed', 'private_sale', 'public_auction', 'transferred_without_cost'])],
            'transfer_entity_name' => ['nullable', 'string', 'max:255'],
            'official_receipt_no' => ['nullable', 'string', 'max:255'],
            'official_receipt_date' => ['nullable', 'date'],
            'official_receipt_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $trim = static function ($value) {
            if (!is_string($value)) {
                return $value;
            }

            $value = trim($value);
            return $value === '' ? null : $value;
        };

        $this->merge([
            'quantity' => $this->input('quantity'),
            'disposal_method' => $trim($this->input('disposal_method')),
            'transfer_entity_name' => $trim($this->input('transfer_entity_name')),
            'official_receipt_no' => $trim($this->input('official_receipt_no')),
            'official_receipt_date' => $trim($this->input('official_receipt_date')),
            'official_receipt_amount' => $trim($this->input('official_receipt_amount')),
        ]);
    }
}

