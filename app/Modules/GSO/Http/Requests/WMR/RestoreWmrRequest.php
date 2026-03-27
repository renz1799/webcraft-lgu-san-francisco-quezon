<?php

namespace App\Modules\GSO\Http\Requests\WMR;

use Illuminate\Foundation\Http\FormRequest;

class RestoreWmrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Administrator')
            || $this->user()?->can('modify Allow Data Restoration')
            || $this->user()?->can('restore WMR');
    }

    public function rules(): array
    {
        return [];
    }
}

