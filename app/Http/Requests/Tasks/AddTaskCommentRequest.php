<?php

namespace App\Http\Requests\Tasks;

use Illuminate\Foundation\Http\FormRequest;

class AddTaskCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && $user->hasAnyRole(['Administrator', 'admin', 'Staff']);
    }

    public function rules(): array
    {
        return [
            'note' => ['required', 'string'],
        ];
    }
}
