<?php

namespace App\Modules\GSO\Http\Requests\RIS;

use Illuminate\Foundation\Http\FormRequest;

class RestoreRisRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();
        if (!$u) return false;

        return $u->hasRole('Administrator')
            || $u->can('modify Allow Data Restoration')
            || $u->can('restore RIS');
    }

    public function rules(): array
    {
        return [];
    }
}