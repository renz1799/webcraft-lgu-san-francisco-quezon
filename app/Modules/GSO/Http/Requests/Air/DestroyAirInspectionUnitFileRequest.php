<?php

namespace App\Modules\GSO\Http\Requests\Air;

use Illuminate\Foundation\Http\FormRequest;

class DestroyAirInspectionUnitFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsAnyPermission($user, [
            'air.inspect',
            'air.manage_files',
        ]);
    }

    public function rules(): array
    {
        return [];
    }
}
