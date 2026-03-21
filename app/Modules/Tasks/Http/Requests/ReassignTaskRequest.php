<?php

namespace App\Modules\Tasks\Http\Requests;

use App\Core\Support\CurrentContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReassignTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && (
            $user->hasAnyRole(['Administrator', 'admin'])
            || $user->can('modify Reassign Tasks')
        );
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
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function currentModuleId(): ?string
    {
        return app(CurrentContext::class)->moduleId();
    }
}
