<?php

namespace App\Modules\GSO\Http\Requests\Air;

use Illuminate\Foundation\Http\FormRequest;

class SuggestAirItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsAnyPermission($user, [
            'air.view',
            'air.manage_items',
        ]);
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
        ];
    }
}
