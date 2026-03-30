<?php

namespace App\Modules\GSO\Http\Requests\RIS\Items;

use Illuminate\Foundation\Http\FormRequest;

class SuggestRisItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();

        return (bool) $u && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsAnyPermission($u, [
            'ris.view',
            'ris.manage_items',
            'ris.create',
            'ris.update',
        ]);
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:200'],
        ];
    }
}
