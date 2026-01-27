<?php

namespace App\Http\Requests\Tasks;

use Illuminate\Foundation\Http\FormRequest;

class AddTaskCommentRequest extends FormRequest
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
            'note' => ['required', 'string'],
        ];
    }
}
