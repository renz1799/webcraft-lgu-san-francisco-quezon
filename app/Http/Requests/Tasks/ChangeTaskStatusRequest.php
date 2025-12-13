<?php

namespace App\Http\Requests\Tasks;

use Illuminate\Foundation\Http\FormRequest;

class ChangeTaskStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        // later: permission or policy checks
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:pending,in_progress,done,cancelled'],
            'note' => ['nullable', 'string'],
        ];
    }
}
