<?php

namespace App\Modules\GSO\Http\Requests\Air;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveAirInspectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Administrator', 'admin'])
            || $this->user()?->can('modify AIR');
    }

    public function rules(): array
    {
        return [
            'invoice_number' => ['required', 'string', 'max:255'],
            'invoice_date' => ['required', 'date'],
            'date_received' => ['required', 'date'],
            'received_completeness' => ['required', Rule::in(['complete', 'partial'])],
            'received_notes' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['required', 'uuid'],
            'items.*.description_snapshot' => ['nullable', 'string', 'max:5000'],
            'items.*.qty_delivered' => ['required', 'integer', 'min:0'],
            'items.*.qty_accepted' => ['required', 'integer', 'min:0'],
            'items.*.remarks' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
