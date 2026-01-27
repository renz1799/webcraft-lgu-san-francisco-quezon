<?php

namespace App\Http\Requests\Tasks;

use Illuminate\Foundation\Http\FormRequest;

class ReassignTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();

        return $u
            && ($u->hasAnyRole(['Administrator', 'Staff']));
    }

    public function rules(): array
    {
        return [
            'assignee_user_id' => ['required', 'uuid'],
            'note' => ['nullable', 'string'],
        ];
    }
}
