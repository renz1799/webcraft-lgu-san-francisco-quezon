<?php

namespace App\Modules\GSO\Http\Requests\Air;

use Illuminate\Foundation\Http\FormRequest;

class CreateAirFollowUpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Administrator', 'admin'])
            || $this->user()?->can('modify AIR')
            || $this->user()?->can('create AIR');
    }

    public function rules(): array
    {
        return [];
    }
}
