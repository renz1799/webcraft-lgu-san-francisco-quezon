<?php

namespace App\Modules\GSO\Http\Requests\FundClusters;

use App\Http\Requests\BaseFormRequest;
use App\Modules\GSO\Http\Requests\Concerns\AuthorizesGsoPermissions;
use Illuminate\Validation\Rule;

class UpdateFundClusterRequest extends BaseFormRequest
{
    use AuthorizesGsoPermissions;

    public function authorize(): bool
    {
        return $this->allowsGsoPermission('fund_clusters.update');
    }

    public function rules(): array
    {
        $fundClusterId = (string) $this->route('fundCluster');

        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('fund_clusters', 'code')->ignore($fundClusterId, 'id')],
            'name' => ['required', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
