<?php

namespace App\Modules\GSO\Http\Requests\WMR;

use Illuminate\Foundation\Http\FormRequest;

class ReopenWmrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Administrator')
            || $this->user()?->hasRole('Staff')
            || $this->user()?->can('modify WMR');
    }

    public function rules(): array
    {
        return [];
    }
}
