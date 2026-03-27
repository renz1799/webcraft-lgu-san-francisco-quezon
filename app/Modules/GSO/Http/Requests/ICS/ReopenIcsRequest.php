<?php

namespace App\Modules\GSO\Http\Requests\ICS;

use Illuminate\Foundation\Http\FormRequest;

class ReopenIcsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Administrator', 'admin'])
            || $this->user()?->hasRole('Staff')
            || $this->user()?->can('modify ICS');
    }

    public function rules(): array
    {
        return [];
    }
}
