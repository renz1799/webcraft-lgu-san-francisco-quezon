<?php

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class UserPermissionsTableRequest extends FormRequest
{
    public function authorize(): bool
    {
        // your route group is role:Administrator already, but keep this consistent
        return $this->user()?->hasRole('Administrator') === true;
    }

    public function rules(): array
    {
        return [
            'page' => ['nullable','integer','min:1'],
            'size' => ['nullable','integer','min:1','max:200'],
            'q'    => ['nullable','string','max:200'],
        ];
    }

    public function filters(): array
    {
        return [
            'page' => (int) ($this->input('page', 1)),
            'size' => (int) ($this->input('size', 20)),
            'q'    => trim((string) $this->input('q', '')),
        ];
    }
}
