<?php

namespace App\Core\Http\Requests\Roles;

use App\Core\Support\AdminContextAuthorizer;
use App\Core\Support\CurrentContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
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
            'name'                  => [
                'required',
                'string',
                'max:255',
                Rule::unique('roles', 'name')
                    ->where('module_id', $moduleId)
                    ->where('guard_name', 'web')
                    ->whereNull('deleted_at'),
            ],
            'permissions'           => ['array'],
            'permissions.*'         => [
                'uuid',
                Rule::exists('permissions', 'id')
                    ->where('module_id', $moduleId)
                    ->where('guard_name', 'web')
                    ->whereNull('deleted_at'),
            ],
        ];
    }

    private function currentModuleId(): ?string
    {
        return app(CurrentContext::class)->moduleId();
    }
}
