<?php

namespace App\Modules\GSO\Http\Requests\FundSources;

use App\Http\Requests\BaseFormRequest;

class DestroyFundSourceRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user
            && ($user->hasAnyRole(['Administrator', 'admin']) || $user->can('modify Fund Sources'));
    }

    public function rules(): array
    {
        return [];
    }
}
