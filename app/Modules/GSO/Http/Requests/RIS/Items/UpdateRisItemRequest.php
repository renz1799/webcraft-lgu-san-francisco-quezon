<?php

namespace App\Modules\GSO\Http\Requests\RIS\Items;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRisItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();

        return (bool) $u && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsAnyPermission($u, [
            'ris.manage_items',
            'ris.create',
            'ris.update',
        ]);
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
