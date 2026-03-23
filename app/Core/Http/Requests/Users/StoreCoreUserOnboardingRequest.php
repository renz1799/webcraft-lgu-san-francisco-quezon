<?php

namespace App\Core\Http\Requests\Users;

use App\Core\Services\Contracts\Access\ModuleDepartmentResolverInterface;
use App\Core\Support\AdminContextAuthorizer;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class StoreCoreUserOnboardingRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return app(AdminContextAuthorizer::class)->canRegisterUsers($this->user());
    }

    public function rules(): array
    {
        $selectedModuleId = trim((string) $this->input('module_id'));
        $departmentResolver = app(ModuleDepartmentResolverInterface::class);

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
            ],
            'department_id' => [
                'bail',
                'required',
                'uuid',
                Rule::exists('departments', 'id')->where(function ($query) {
                    $query->where('is_active', true)
                        ->whereNull('deleted_at');
                }),
                function (string $attribute, mixed $value, \Closure $fail) use ($selectedModuleId, $departmentResolver): void {
                    if ($selectedModuleId === '') {
                        $fail('Select a module first.');

                        return;
                    }

                    if (! $departmentResolver->departmentBelongsToModule((string) $value, $selectedModuleId)) {
                        $fail('The selected department is not allowed for that module.');
                    }
                },
            ],
            'role' => [
                'bail',
                'required',
                'string',
                Rule::exists('roles', 'name')->where(function ($query) use ($selectedModuleId) {
                    $query->where('module_id', $selectedModuleId)
                        ->where('guard_name', 'web')
                        ->whereNull('deleted_at');
                }),
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
