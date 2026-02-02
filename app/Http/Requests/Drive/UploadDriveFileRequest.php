<?php

namespace App\Http\Requests\Drive;

use Illuminate\Foundation\Http\FormRequest;

class UploadDriveFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        // later:
        // return $this->user()->can('create Drive Files');
        return true;
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
