<?php

namespace App\Core\Http\Requests\Tasks;

use App\Core\Support\CurrentContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
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
        $moduleId = $this->taskOwnerModuleId() ?: $this->currentModuleId();

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

    private function taskOwnerModuleId(): ?string
    {
        $taskId = trim((string) ($this->route('id') ?? $this->route('task') ?? ''));

        if ($taskId === '') {
            return null;
        }

        $moduleId = DB::table('tasks')
            ->where('id', $taskId)
            ->value('module_id');

        return is_string($moduleId) && $moduleId !== '' ? $moduleId : null;
    }
}
