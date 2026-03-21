<?php

namespace App\Core\Http\Requests\Drive;

use Illuminate\Foundation\Http\FormRequest;

class DisconnectDriveRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();

        return (bool) $u && (
            $u->hasAnyRole(['Administrator', 'admin'])
            || $u->can('modify Google Drive Connection')
        );
    }

    public function rules(): array
    {
        return [];
    }
}
