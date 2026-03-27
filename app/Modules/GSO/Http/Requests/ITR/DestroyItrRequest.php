<?php

namespace App\Modules\GSO\Http\Requests\ITR;

use Illuminate\Foundation\Http\FormRequest;

class DestroyItrRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Administrator')
            || $this->user()?->can('modify ITR');
    }

    public function rules(): array
    {
        return [];
    }
}


