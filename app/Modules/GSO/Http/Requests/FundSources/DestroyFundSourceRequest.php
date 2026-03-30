<?php

namespace App\Modules\GSO\Http\Requests\FundSources;

use App\Http\Requests\BaseFormRequest;
use App\Modules\GSO\Http\Requests\Concerns\AuthorizesGsoPermissions;

class DestroyFundSourceRequest extends BaseFormRequest
{
    use AuthorizesGsoPermissions;

    public function authorize(): bool
    {
        return $this->allowsGsoPermission('fund_sources.archive');
    }

    public function rules(): array
    {
        return [];
    }
}
