<?php

namespace App\Modules\GSO\Http\Requests\FundClusters;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateFundClusterRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user
            && ($user->hasAnyRole(['Administrator', 'admin']) || $user->can('modify Fund Clusters'));
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
