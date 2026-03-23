<?php

namespace App\Core\Http\Requests\Drive;

use App\Core\Models\Module;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConnectDriveRequest extends FormRequest
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
        return [
            'module_id' => [
                'required',
                'uuid',
                Rule::exists(Module::class, 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
        ];
    }
}
