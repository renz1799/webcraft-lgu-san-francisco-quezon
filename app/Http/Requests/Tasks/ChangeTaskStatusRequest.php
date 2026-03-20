<?php

namespace App\Http\Requests\Tasks;

use Illuminate\Foundation\Http\FormRequest;

class ChangeTaskStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && $user->hasAnyRole(['Administrator', 'admin', 'Staff']);
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:pending,in_progress,done,cancelled'],
            'note' => ['nullable', 'string'],
        ];
    }
}
