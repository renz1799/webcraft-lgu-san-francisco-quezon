<?php

namespace App\Modules\GSO\Http\Requests\PTR;

use Illuminate\Foundation\Http\FormRequest;

class FinalizePtrRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsPermission($user, 'ptr.finalize');
    }

    public function rules(): array
    {
        return [];
    }
}
