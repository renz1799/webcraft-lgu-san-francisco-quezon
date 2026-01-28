<?php

namespace App\Http\Requests\Tasks;

use Illuminate\Foundation\Http\FormRequest;

class ReassignTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();
        if (!$u) return false;

        // ✅ must be Admin AND have permission
        return $u->hasRole('Administrator') || $u->can('modify Reassign Tasks');
    }

    public function rules(): array
    {
        return [
            'assignee_user_id' => ['required', 'uuid'],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
