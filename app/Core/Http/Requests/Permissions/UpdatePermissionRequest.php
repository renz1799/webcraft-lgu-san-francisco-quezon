<?php

namespace App\Core\Http\Requests\Permissions;

use App\Http\Requests\BaseFormRequest;
use App\Core\Models\Permission;
use App\Core\Support\CurrentContext;
use Illuminate\Validation\Rule;

class UpdatePermissionRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();

        return $u
            && $u->hasAnyRole(['Administrator', 'admin'])
            && $this->currentModuleId() !== null;
    }

    public function rules(): array
    {
        $permission = $this->route('permission');
        $permissionId = $permission instanceof Permission ? (string) $permission->id : null;
        $guard = $this->input('guard_name', 'web');
        $moduleId = $this->currentModuleId() ?? '__missing_module__';

        return [
            'name' => [
                'bail', 'required', 'string', 'max:255',
                'regex:/^(view|modify|delete)\s+.+$/i',
                Rule::unique('permissions', 'name')
                    ->ignore($permissionId, 'id')
                    ->where('module_id', $moduleId)
                    ->where('guard_name', $guard)
                    ->whereNull('deleted_at'),
            ],
            'page' => ['bail', 'required', 'string', 'max:255'],
            'guard_name' => ['bail', 'sometimes', 'in:web,api'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $name = trim((string) $this->input('name', ''));
        $guard = $this->input('guard_name', 'web');

        $page = $this->input('page');
        if (! $page && $name) {
            $parts = preg_split('/\s+/', $name, 2);
            $page = $parts[1] ?? null;
        }

        if ($name && preg_match('/^(view|modify|delete)\s+(.+)$/i', $name, $m)) {
            $action = strtolower($m[1]);
            $rest = $m[2];
            $name = "{$action} {$rest}";
        }

        $this->merge([
            'name' => $name,
            'page' => $page ? trim($page) : null,
            'guard_name' => $guard,
        ]);
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Name must start with "view", "modify", or "delete", e.g. "view Login Logs".',
        ];
    }

    private function currentModuleId(): ?string
    {
        return app(CurrentContext::class)->moduleId();
    }
}
