<?php

namespace App\Modules\GSO\Http\Requests\RIS;

use Illuminate\Foundation\Http\FormRequest;

class DeleteRisRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();
        if (!$u) return false;

        // ✅ follow your permission convention
        return $u->hasRole('Administrator') || $u->can('delete RIS');
    }

    public function rules(): array
    {
        return [];
    }
}