<?php

namespace App\Modules\GSO\Http\Requests\ITR\Items;

use Illuminate\Foundation\Http\FormRequest;

class SuggestItrItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsAnyPermission($user, [
            'itr.view',
            'itr.manage_items',
        ]);
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
        ];
    }
}
