<?php

namespace App\Modules\GSO\Http\Requests\ICS;

use Illuminate\Foundation\Http\FormRequest;

class RestoreIcsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Administrator', 'admin'])
            || $this->user()?->can('modify Allow Data Restoration')
            || $this->user()?->can('restore ICS');
    }

    public function rules(): array
    {
        return [];
    }
}
