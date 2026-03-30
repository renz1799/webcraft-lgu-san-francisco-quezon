<?php

namespace App\Modules\GSO\Http\Requests\PTR\Items;

use Illuminate\Foundation\Http\FormRequest;

class RemovePtrItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsPermission($user, 'ptr.manage_items');
    }

    public function rules(): array
    {
        return [];
    }
}
