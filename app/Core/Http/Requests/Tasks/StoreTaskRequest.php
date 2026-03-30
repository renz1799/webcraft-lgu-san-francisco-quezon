<?php

namespace App\Core\Http\Requests\Tasks;

use App\Core\Support\AdminContextAuthorizer;
use App\Core\Support\CurrentContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user
            && app(AdminContextAuthorizer::class)->allowsPermission($user, 'tasks.create');
    }

    public function rules(): array
    {
        $moduleId = $this->currentModuleId();

        return [
            'assignee_user_id' => [
                'required',
                'uuid',
                Rule::exists('users', 'id')->where(function ($query) use ($moduleId) {
                    $query->where('is_active', true)
                        ->whereExists(function ($subQuery) use ($moduleId) {
                            $subQuery->selectRaw('1')
                                ->from('user_modules')
                                ->whereColumn('user_modules.user_id', 'users.id')
                                ->where('user_modules.module_id', $moduleId)
                                ->where('user_modules.is_active', true);
                        });
                }),
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['nullable', 'string', 'max:100'],
            'subject_type' => ['nullable', 'string', 'max:100'],
            'subject_id' => ['nullable', 'uuid'],
            'data' => ['nullable', 'array'],
        ];
    }

    private function currentModuleId(): ?string
    {
        return app(CurrentContext::class)->moduleId();
    }
}
