<?php

namespace App\Modules\GSO\Http\Requests\RIS\Items;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRisItemRequest extends FormRequest
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
            // qty_requested is BASE qty
            'qty_requested' => ['sometimes', 'integer', 'min:0'],
            'remarks' => ['sometimes', 'nullable', 'string'],
        ];
    }
}