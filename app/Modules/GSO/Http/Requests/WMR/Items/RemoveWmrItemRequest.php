<?php

namespace App\Modules\GSO\Http\Requests\WMR\Items;

use Illuminate\Foundation\Http\FormRequest;

class RemoveWmrItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsPermission($user, 'wmr.manage_items');
    }
    public function rules(): array
    {
        return [];
    }
}

