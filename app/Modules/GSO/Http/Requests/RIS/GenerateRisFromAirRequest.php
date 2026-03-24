<?php

namespace App\Modules\GSO\Http\Requests\RIS;

use Illuminate\Foundation\Http\FormRequest;

class GenerateRisFromAirRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();
        if (!$u) return false;

        // adjust to your permissions
        return $u->hasRole('Administrator') || $u->can('create RIS') || $u->can('modify RIS');
    }

    public function rules(): array
    {
        return [];
    }
}
