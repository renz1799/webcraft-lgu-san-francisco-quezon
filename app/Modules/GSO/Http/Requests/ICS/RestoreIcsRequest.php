<?php

namespace App\Modules\GSO\Http\Requests\ICS;

use Illuminate\Foundation\Http\FormRequest;

class RestoreIcsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsAnyPermission($user, [
            'ics.restore',
            'audit_logs.restore_data',
        ]);
    }

    public function rules(): array
    {
        return [];
    }
}
