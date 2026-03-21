<?php

namespace App\Core\Http\Requests\AuditLogs;

use App\Core\Models\Permission;
use App\Core\Models\Role;
use App\Core\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class AuditLogPrintRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user && (
            $user->hasAnyRole(['Administrator', 'admin']) ||
            $user->can('view Audit Logs')
        );
    }

    public function rules(): array
    {
        return [
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'module' => ['nullable', 'string', 'max:100'],
            'action' => ['nullable', 'string', 'max:150'],
            'actor_id' => ['nullable', 'uuid'],
            'subject_type' => ['nullable', 'in:user,permission,role'],
            'search' => ['nullable', 'string', 'max:255'],
            'paper_profile' => ['nullable', 'string', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $clean = [];

        foreach ($this->all() as $key => $value) {
            if (! is_string($value)) {
                $clean[$key] = $value;
                continue;
            }

            $value = trim($value);
            $clean[$key] = $value === '' ? null : $value;
        }

        $clean['module'] = $clean['module'] ?? ($clean['module_name'] ?? null);
        $clean['subject_type'] = $this->normalizeSubjectType($clean['subject_type'] ?? null);

        unset($clean['module_name']);

        $this->replace($clean);
    }

    private function normalizeSubjectType(mixed $value): ?string
    {
        $subjectType = trim((string) $value);

        if ($subjectType === '') {
            return null;
        }

        return match ($subjectType) {
            User::class => 'user',
            Permission::class => 'permission',
            Role::class => 'role',
            default => strtolower($subjectType),
        };
    }
}
