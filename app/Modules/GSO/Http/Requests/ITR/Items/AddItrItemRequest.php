<?php

namespace App\Modules\GSO\Http\Requests\ITR\Items;

use Illuminate\Foundation\Http\FormRequest;

class AddItrItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsPermission($user, 'itr.manage_items');
    }

    public function rules(): array
    {
        return [
            'inventory_item_id' => ['required', 'uuid', 'exists:inventory_items,id'],
        ];
    }
}
