<?php

namespace App\Http\Requests\Tasks;

use Illuminate\Foundation\Http\FormRequest;

class ReassignTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        // later: return $this->user()->can('modify Task Assignments');
        return true;
    }

    public function rules(): array
    {
        return [
            'assignee_user_id' => ['required', 'uuid'],
            'note' => ['nullable', 'string'],
        ];
    }
}
