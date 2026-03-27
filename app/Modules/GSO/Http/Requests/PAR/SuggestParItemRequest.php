<?php

namespace App\Modules\GSO\Http\Requests\PAR;

use Illuminate\Foundation\Http\FormRequest;

class SuggestParItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Administrator', 'admin'])
            || $this->user()?->hasRole('Staff')
            || $this->user()?->can('modify PAR');
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:80'],
        ];
    }
}
