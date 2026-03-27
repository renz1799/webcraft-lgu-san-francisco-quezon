<?php

namespace App\Modules\GSO\Http\Requests\WMR\Items;

use Illuminate\Foundation\Http\FormRequest;

class SuggestWmrItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Administrator')
            || $this->user()?->hasRole('Staff')
            || $this->user()?->can('modify WMR')
            || $this->user()?->can('view WMR');
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
        ];
    }
}

