<?php

namespace App\Modules\GSO\Http\Requests\ITR;

use Illuminate\Foundation\Http\FormRequest;

class RestoreItrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Administrator')
            || $this->user()?->can('modify Allow Data Restoration')
            || $this->user()?->can('restore ITR');
    }

    public function rules(): array
    {
        return [];
    }
}


