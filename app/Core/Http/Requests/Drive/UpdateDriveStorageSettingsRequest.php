<?php

namespace App\Core\Http\Requests\Drive;

use App\Core\Models\Module;
use App\Core\Support\AdminContextAuthorizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDriveStorageSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();

        return (bool) $u
            && app(AdminContextAuthorizer::class)->allowsPermission($u, 'drive_connections.connect');
    }

    public function rules(): array
    {
        return [
            'module_id' => [
                'required',
                'uuid',
                Rule::exists(Module::class, 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'storage' => ['required', 'array'],
            'storage.*' => ['nullable', 'string', 'max:255'],
        ];
    }
}
