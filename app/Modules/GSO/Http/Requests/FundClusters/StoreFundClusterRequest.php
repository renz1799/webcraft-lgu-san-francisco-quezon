<?php

namespace App\Modules\GSO\Http\Requests\FundClusters;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class StoreFundClusterRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user
            && ($user->hasAnyRole(['Administrator', 'admin']) || $user->can('modify Fund Clusters'));
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', Rule::unique('fund_clusters', 'code')],
            'name' => ['required', 'string', 'max:150'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
