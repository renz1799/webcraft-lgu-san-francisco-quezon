<?php

namespace App\Http\Requests\Permissions;

use App\Http\Requests\BaseFormRequest;
use App\Models\Permission;

class DestroyPermissionRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();
        return $u && ($u->hasRole('Administrator'));
    }

    public function rules(): array
    {
        return [];
    }

    /**
     * Optional: block deletion if the permission is still attached to roles.
     * Remove this method if you don't want this safeguard.
     */
    public function withValidator($validator): void
    {
        $permission = $this->route('permission');
        if ($permission instanceof Permission) {
            $validator->after(function ($v) use ($permission) {
                if ($permission->roles()->exists()) {
                    $v->errors()->add('permission', 'This permission is assigned to one or more roles.');
                }
            });
        }
    }
}
