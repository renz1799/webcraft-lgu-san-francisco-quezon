<?php

namespace App\Core\Http\Requests\Drive;

use App\Core\Support\AdminContextAuthorizer;
use Illuminate\Foundation\Http\FormRequest;

class UploadDriveFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()
            && app(AdminContextAuthorizer::class)->allowsPermission($this->user(), 'drive_files.create');
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:10240', // 10MB
                'mimetypes:image/jpeg,image/png,image/webp,application/pdf',
            ],
            'make_public' => ['sometimes', 'boolean'],
        ];
    }
}
