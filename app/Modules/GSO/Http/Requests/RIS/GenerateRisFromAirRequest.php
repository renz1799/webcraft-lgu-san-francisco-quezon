<?php

namespace App\Modules\GSO\Http\Requests\RIS;

use Illuminate\Foundation\Http\FormRequest;

class GenerateRisFromAirRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();

        return (bool) $u && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsAnyPermission($u, [
            'ris.generate_from_air',
            'ris.create',
            'ris.update',
        ]);
    }

    public function rules(): array
    {
        return [];
    }
}
