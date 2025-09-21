<?php

namespace App\Http\Requests\Users;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateUserModulePermissionsRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();
        return $u && ($u->hasRole('admin') || $u->can('modify users'));
    }

    public function rules(): array
    {
        return [
            // optional role change
            'role' => ['sometimes','nullable','string', Rule::exists('roles','name')->where('guard_name','web')],

            // ✅ allow “no permissions”:
            // - if present, must be an array
            // - may be empty
            // - may also be entirely absent (e.g., role change only)
            'permissions'       => ['sometimes','array'],
            'permissions.*'     => ['array'],
            'permissions.*.*'   => ['array'],
            // accept common synonyms too; service will normalize
            'permissions.*.*.*' => ['string','in:view,create,update,edit,modify,delete,export'],
        ];
    }
}