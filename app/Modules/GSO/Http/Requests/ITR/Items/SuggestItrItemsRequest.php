<?php

namespace App\Modules\GSO\Http\Requests\ITR\Items;

use Illuminate\Foundation\Http\FormRequest;

class SuggestItrItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Administrator')
            || $this->user()?->hasRole('Staff')
            || $this->user()?->can('modify ITR')
            || $this->user()?->can('view ITR');
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
        ];
    }
}



