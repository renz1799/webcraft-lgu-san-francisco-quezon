<?php

namespace App\Modules\GSO\Http\Requests\FundClusters;

use App\Http\Requests\BaseFormRequest;
use App\Modules\GSO\Http\Requests\Concerns\AuthorizesGsoPermissions;

class DestroyFundClusterRequest extends BaseFormRequest
{
    use AuthorizesGsoPermissions;

    public function authorize(): bool
    {
        return $this->allowsGsoPermission('fund_clusters.archive');
    }

    public function rules(): array
    {
        return [];
    }
}
