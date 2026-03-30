<?php

namespace App\Core\Http\Requests\Files;

use App\Core\Support\AdminContextAuthorizer;
use Illuminate\Foundation\Http\FormRequest;

class StoreDriveFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()
            && app(AdminContextAuthorizer::class)->allowsPermission($this->user(), 'drive_files.create');
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:10240'], // 10MB example
            'make_public' => ['sometimes', 'boolean'],
        ];
    }
}
