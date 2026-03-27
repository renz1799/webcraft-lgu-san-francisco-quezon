<?php

namespace App\Modules\GSO\Http\Requests\ICS\Items;

use Illuminate\Foundation\Http\FormRequest;

class SuggestIcsItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Administrator', 'admin'])
            || $this->user()?->hasRole('Staff')
            || $this->user()?->can('modify ICS')
            || $this->user()?->can('view ICS');
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
        ];
    }
}
