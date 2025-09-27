<?php

namespace App\Http\Requests\Logs;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Permission;
use App\Models\Role;

class RestoreSubjectRequest extends FormRequest
{
    public function rules(): array
    {
        // we accept any string here; mapping/whitelisting happens in authorize()
        return [
            'type' => ['required', 'string'],
            'id'   => ['required', 'uuid'],
        ];
    }

    public function authorize(): bool
    {
        $actor = $this->user();

        if (! $actor) {
            Log::warning('audit.restore: unauthorized (no actor)');
            return false;
        }

        // ALLOW: admin role OR the specific permission
        if (! ($actor->hasRole('admin') || $actor->can('modify Allow Data Restoration'))) {
            Log::warning('audit.restore: actor not allowed', [
                'actor_id' => $actor->id,
                'roles'    => $actor->getRoleNames()->all(),
                'perms'    => $actor->getAllPermissions()->pluck('name')->all(),
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

        $exists = $class::withTrashed()->whereKey($this->input('id'))->exists();
        if (! $exists) {
            Log::warning('audit.restore: subject not found', [
                'class' => $class,
                'id'    => $this->input('id'),
            ]);
            return false;
        }

        return true;
    }

    /** Controller uses this to get the subject model instance. */
    public function model()
    {
        $class = $this->resolveClass();
        return $class ? $class::withTrashed()->findOrFail($this->input('id')) : null;
    }

    /** Whitelist of restorable types. Add more as needed. */
    protected function typeMap(): array
    {
        return [
            'user'       => User::class,
            'permission' => Permission::class,
            'role'       => Role::class,
        ];
    }

    /** Accept both short keys (user) and FQCN (App\Models\User) but only if whitelisted. */
    protected function resolveClass(): ?string
    {
        $type = (string) $this->input('type');
        $map  = $this->typeMap();

        // short key
        if (isset($map[$type])) {
            return $map[$type];
        }

        // FQCN (must match one of the whitelisted classes)
        if (in_array($type, $map, true)) {
            return $type;
        }

        return null;
    }
}
