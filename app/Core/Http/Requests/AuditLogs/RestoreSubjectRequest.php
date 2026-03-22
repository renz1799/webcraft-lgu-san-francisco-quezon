<?php

namespace App\Core\Http\Requests\AuditLogs;

use App\Core\Models\Permission;
use App\Core\Models\Role;
use App\Core\Models\User;
use App\Core\Support\AdminRouteResolver;
use App\Core\Support\AdminContextAuthorizer;
use App\Core\Support\CurrentContext;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class RestoreSubjectRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', 'string'],
            'id' => ['required', 'uuid'],
        ];
    }

    public function authorize(): bool
    {
        $actor = $this->user();

        if (! $actor) {
            Log::warning('audit.restore: unauthorized (no actor)');
            return false;
        }

        if (! app(AdminContextAuthorizer::class)->canRestoreCurrentContextAuditData($actor)) {
            Log::warning('audit.restore: actor not allowed', [
                'actor_id' => $actor->id,
                'roles' => $actor->getRoleNames()->all(),
                'perms' => $actor->getAllPermissions()->pluck('name')->all(),
            ]);
            return false;
        }

        $class = $this->resolveClass();
        if (! $class) {
            Log::warning('audit.restore: unknown type', ['input_type' => $this->input('type')]);
            return false;
        }

        if (! in_array(SoftDeletes::class, class_uses_recursive($class), true)) {
            Log::warning('audit.restore: class not soft-deletable', ['class' => $class]);
            return false;
        }

        if ($class === User::class && app(AdminRouteResolver::class)->isModuleScoped()) {
            Log::warning('audit.restore: user restore is core-only in module context', [
                'id' => $this->input('id'),
            ]);

            return false;
        }

        $query = $this->subjectQuery($class);
        if (! $query) {
            Log::warning('audit.restore: missing module context', ['class' => $class]);
            return false;
        }

        $exists = $query->whereKey($this->input('id'))->exists();
        if (! $exists) {
            Log::warning('audit.restore: subject not found', [
                'class' => $class,
                'id' => $this->input('id'),
            ]);
            return false;
        }

        return true;
    }

    public function model()
    {
        $class = $this->resolveClass();

        return $class
            ? $this->subjectQuery($class)?->findOrFail($this->input('id'))
            : null;
    }

    protected function typeMap(): array
    {
        return [
            'user' => User::class,
            'permission' => Permission::class,
            'role' => Role::class,
        ];
    }

    protected function resolveClass(): ?string
    {
        $type = (string) $this->input('type');
        $map = $this->typeMap();

        if (isset($map[$type])) {
            return $map[$type];
        }

        if (in_array($type, $map, true)) {
            return $type;
        }

        return null;
    }

    protected function subjectQuery(string $class)
    {
        $query = $class::withTrashed();

        if (! $this->requiresModuleScope($class)) {
            return $query;
        }

        $moduleId = $this->currentContext()->moduleId();
        if (! $moduleId) {
            return null;
        }

        return $query->where('module_id', $moduleId);
    }

    protected function requiresModuleScope(string $class): bool
    {
        if (in_array($class, [Permission::class, Role::class], true)) {
            return true;
        }

        return false;
    }

    protected function currentContext(): CurrentContext
    {
        return app(CurrentContext::class);
    }
}
