<?php

namespace App\Http\Requests\Tasks;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['nullable', 'string', 'max:100'],
            'subject_type' => ['nullable', 'string', 'max:100'],
            'subject_id' => ['nullable', 'uuid'],
            'data' => ['nullable', 'array'],
        ];
    }
}
