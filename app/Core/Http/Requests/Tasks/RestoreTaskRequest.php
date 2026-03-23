<?php

namespace App\Core\Http\Requests\Tasks;

use Illuminate\Foundation\Http\FormRequest;

class RestoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && $user->hasAnyRole(['Administrator', 'admin']);
    }

    public function rules(): array
    {
        return [];
    }
}
