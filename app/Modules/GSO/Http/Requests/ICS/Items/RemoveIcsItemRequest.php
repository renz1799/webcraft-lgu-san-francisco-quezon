<?php

namespace App\Modules\GSO\Http\Requests\ICS\Items;

use Illuminate\Foundation\Http\FormRequest;

class RemoveIcsItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsPermission($user, 'ics.manage_items');
    }

    public function rules(): array
    {
        return [];
    }
}
