<?php

namespace App\Modules\GSO\Http\Requests\ITR\Items;

use Illuminate\Foundation\Http\FormRequest;

class RemoveItrItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('Administrator')
            || $this->user()?->hasRole('Staff')
            || $this->user()?->can('modify ITR');
    }

    public function rules(): array
    {
        return [];
    }
}



