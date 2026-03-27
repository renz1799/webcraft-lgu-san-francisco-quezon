<?php

namespace App\Modules\GSO\Http\Requests\PTR\Items;

use Illuminate\Foundation\Http\FormRequest;

class SuggestPtrItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Administrator')
            || $this->user()?->hasRole('Staff')
            || $this->user()?->can('modify PTR')
            || $this->user()?->can('view PTR');
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
        ];
    }
}
