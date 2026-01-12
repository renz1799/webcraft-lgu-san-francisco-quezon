<?php

namespace App\Http\Requests\Users;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class UserAccessRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();
        return (bool) $this->user()?->hasRole('Administrator');
    }

    public function rules(): array
    {
        return [
            // role is optional; if omitted we only sync permissions
            'role' => ['sometimes', 'nullable', 'string', Rule::exists('roles','name')->where('guard_name','web')],
            // permissions come as names (e.g. "view Users")
            'permissions'   => ['sometimes', 'array'],
            'permissions.*' => ['string', Rule::exists('permissions','name')->where('guard_name','web')],
        ];
    }
}
