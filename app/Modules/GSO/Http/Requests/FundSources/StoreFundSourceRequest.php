<?php

namespace App\Modules\GSO\Http\Requests\FundSources;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class StoreFundSourceRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user
            && ($user->hasAnyRole(['Administrator', 'admin']) || $user->can('modify Fund Sources'));
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:150', Rule::unique('fund_sources', 'name')],
            'code' => ['nullable', 'string', 'max:30'],
            'fund_cluster_id' => ['nullable', 'uuid', Rule::exists('fund_clusters', 'id')->whereNull('deleted_at')],
            'is_active' => ['nullable', 'boolean'],
        ];

        if (trim((string) $this->input('code')) !== '') {
            $rules['code'][] = Rule::unique('fund_sources', 'code');
        }

        return $rules;
    }
}
