<?php

namespace App\Modules\GSO\Http\Requests\Air;

use Illuminate\Foundation\Http\FormRequest;

class RestoreAirRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsPermission($user, 'air.restore');
    }

    public function rules(): array
    {
        return [];
    }
}
