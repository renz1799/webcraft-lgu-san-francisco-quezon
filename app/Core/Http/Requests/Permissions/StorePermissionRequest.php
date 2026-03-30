<?php

namespace App\Core\Http\Requests\Permissions;

use App\Http\Requests\BaseFormRequest;
use App\Core\Support\AdminContextAuthorizer;
use App\Core\Support\CurrentContext;
use App\Core\Support\PermissionNaming;
use Illuminate\Validation\Rule;

class StorePermissionRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return app(AdminContextAuthorizer::class)->canManageCurrentContextAccess($this->user())
            && $this->currentModuleId() !== null;
    }

    public function rules(): array
    {
        $moduleId = $this->currentModuleId() ?? '__missing_module__';

        return [
            'name' => [
                'bail', 'required', 'string', 'max:255',
                'regex:/^[a-z][a-z0-9_]*(?:\.[a-z][a-z0-9_]*)+$/',
                Rule::unique('permissions', 'name')
                    ->where('module_id', $moduleId)
                    ->where('guard_name', $this->input('guard_name', 'web')),
            ],
            'page' => ['bail', 'required', 'string', 'max:255'],
            'guard_name' => ['bail', 'sometimes', 'in:web,api'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $name = PermissionNaming::normalizeKey((string) $this->input('name', ''));
        $guard = $this->input('guard_name', 'web');
        $page = $this->input('page');

        $this->merge([
            'name' => $name,
            'page' => $page ? trim($page) : null,
            'guard_name' => $guard,
        ]);
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Use a normalized permission key like "users.view" or "inventory_items.manage_files".',
        ];
    }

    private function currentModuleId(): ?string
    {
        return app(CurrentContext::class)->moduleId();
    }
}
