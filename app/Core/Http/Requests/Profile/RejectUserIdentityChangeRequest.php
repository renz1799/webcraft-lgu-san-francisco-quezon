<?php

namespace App\Core\Http\Requests\Profile;

use App\Http\Requests\BaseFormRequest;

class RejectUserIdentityChangeRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'review_notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
