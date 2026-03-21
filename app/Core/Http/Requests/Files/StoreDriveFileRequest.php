<?php

namespace App\Core\Http\Requests\Files;

use Illuminate\Foundation\Http\FormRequest;

class StoreDriveFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Add permission naming convention later if needed:
        // return $this->user()->can('create Drive Files');
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:10240'], // 10MB example
            'make_public' => ['sometimes', 'boolean'],
        ];
    }
}
