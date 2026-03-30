<?php

namespace App\Modules\GSO\Http\Requests\ITR\Items;

use Illuminate\Foundation\Http\FormRequest;

class RemoveItrItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsPermission($user, 'itr.manage_items');
    }

    public function rules(): array
    {
        return [];
    }
}
