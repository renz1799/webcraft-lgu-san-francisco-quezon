<?php

namespace App\Core\Http\Requests\Users;

use App\Core\Services\Contracts\Access\ModuleDepartmentResolverInterface;
use App\Core\Support\AdminContextAuthorizer;
use App\Core\Support\CurrentContext;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class StoreModuleStaffRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        $contextModule = app(CurrentContext::class)->module();

        return $contextModule !== null
            && ! $contextModule->isPlatformContext()
            && app(AdminContextAuthorizer::class)->canManageCurrentContextAccess($this->user());
    }

    public function rules(): array
    {
        $moduleId = (string) app(CurrentContext::class)->moduleId();
        $defaultDepartmentId = (string) (app(ModuleDepartmentResolverInterface::class)->defaultDepartmentIdForModule($moduleId) ?? '');

        return [
            'first_name' => ['bail', 'required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['bail', 'required', 'string', 'max:255'],
            'name_extension' => ['nullable', 'string', 'max:50'],
            'email' => ['bail', 'required', 'email:rfc', 'max:255'],
            'module_id' => [
                'bail',
                'required',
                'uuid',
                Rule::exists('modules', 'id')->where(function ($query) {
                    $query->where('is_active', true);
                }),
                function (string $attribute, mixed $value, \Closure $fail) use ($moduleId): void {
                    if ((string) $value !== $moduleId) {
                        $fail('The selected module does not match the active module context.');
                    }
                },
            ],
            'role' => [
                'bail',
                'required',
                'string',
                Rule::exists('roles', 'name')->where(function ($query) use ($moduleId) {
                    $query->where('module_id', $moduleId)
                        ->where('guard_name', 'web')
                        ->whereNull('deleted_at');
                }),
            ],
            'department_id' => [
                'bail',
                'required',
                'uuid',
                Rule::exists('departments', 'id')->where(function ($query) {
                    $query->where('is_active', true)
                        ->whereNull('deleted_at');
                }),
                function (string $attribute, mixed $value, \Closure $fail) use ($defaultDepartmentId): void {
                    if ($defaultDepartmentId === '') {
                        $fail('The active module does not have a default department configured.');

                        return;
                    }

                    if ((string) $value !== $defaultDepartmentId) {
                        $fail('Department must match the active module default department.');
                    }
                },
            ],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name' => 'first name',
            'middle_name' => 'middle name',
            'last_name' => 'last name',
            'name_extension' => 'name extension',
            'module_id' => 'module',
            'department_id' => 'department',
            'is_active' => 'module access status',
        ];
    }
}
