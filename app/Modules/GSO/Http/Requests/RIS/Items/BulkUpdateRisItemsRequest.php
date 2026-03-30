<?php

namespace App\Modules\GSO\Http\Requests\RIS\Items;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdateRisItemsRequest extends FormRequest
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
            'items' => ['required', 'array', 'min:0'],

            'items.*.id' => ['required', 'uuid'],
            'items.*.qty_requested' => ['required', 'integer', 'min:0'], // BASE qty
            'items.*.remarks' => ['nullable', 'string'],
        ];
    }
}
