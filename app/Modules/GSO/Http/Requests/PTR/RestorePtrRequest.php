<?php

namespace App\Modules\GSO\Http\Requests\PTR;

use Illuminate\Foundation\Http\FormRequest;

class RestorePtrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Administrator')
            || $this->user()?->can('modify Allow Data Restoration')
            || $this->user()?->can('restore PTR');
    }

    public function rules(): array
    {
        return [];
    }
}
