<?php

namespace App\Http\Requests\Drive;

use Illuminate\Foundation\Http\FormRequest;

class ConnectDriveRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();

        return $u && ($u->hasRole('Administrator') || $u->can('modify Google Drive Connection'));
    }

    public function rules(): array
    {
        return [];
    }
}
