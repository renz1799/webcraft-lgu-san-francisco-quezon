<?php

namespace App\Modules\GSO\Http\Requests\RIS;

use Illuminate\Foundation\Http\FormRequest;

class RestoreRisRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();

        return (bool) $u && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsAnyPermission($u, [
            'ris.restore',
            'audit_logs.restore_data',
        ]);
    }

    public function rules(): array
    {
        return [];
    }
}
