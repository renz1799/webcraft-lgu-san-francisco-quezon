<?php

namespace App\Modules\GSO\Http\Requests\RIS\Items;

use Illuminate\Foundation\Http\FormRequest;

class AddRisItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();
        if (!$u) return false;

        return $u->hasRole('Administrator') || $u->can('modify RIS') || $u->can('create RIS');
    }

    public function rules(): array
    {
        return [
            'item_id' => ['required', 'uuid'],

            // ✅ fund enforcement (Option A)
            'fund_source_id' => ['nullable', 'uuid'],

            // IMPORTANT: we treat qty_requested as BASE qty in UI
            'qty_requested' => ['nullable', 'integer', 'min:0'],

            'remarks' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $trim = static function ($v) {
            if (!is_string($v)) return $v;
            $v = trim($v);
            return $v === '' ? null : $v;
        };

        $this->merge([
            'item_id' => $trim($this->input('item_id')),
            'fund_source_id' => $trim($this->input('fund_source_id')),
            'remarks' => $trim($this->input('remarks')),
        ]);
    }
}