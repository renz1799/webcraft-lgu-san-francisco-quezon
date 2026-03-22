<?php

namespace App\Modules\GSO\Http\Requests\AssetCategories;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetCategoryRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user
            && ($user->hasAnyRole(['Administrator', 'admin']) || $user->can('modify Asset Categories'));
    }

    public function rules(): array
    {
        $assetCategoryId = (string) $this->route('assetCategory');
        $assetTypeId = (string) $this->input('asset_type_id');

        return [
            'asset_type_id' => [
                'required',
                'uuid',
                Rule::exists('asset_types', 'id')->whereNull('deleted_at'),
            ],
            'asset_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('asset_categories', 'asset_code')
                    ->ignore($assetCategoryId, 'id')
                    ->where(fn ($query) => $query
                        ->where('asset_type_id', $assetTypeId)
                        ->whereNull('deleted_at')),
            ],
            'asset_name' => ['required', 'string', 'max:255'],
            'account_group' => ['nullable', 'string', 'max:255'],
        ];
    }
}
