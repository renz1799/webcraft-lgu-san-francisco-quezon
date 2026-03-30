<?php

namespace App\Modules\GSO\Http\Requests\AssetCategories;

use App\Http\Requests\BaseFormRequest;
use App\Modules\GSO\Http\Requests\Concerns\AuthorizesGsoPermissions;
use Illuminate\Validation\Rule;

class StoreAssetCategoryRequest extends BaseFormRequest
{
    use AuthorizesGsoPermissions;

    public function authorize(): bool
    {
        return $this->allowsGsoPermission('asset_categories.create');
    }

    public function rules(): array
    {
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
                    ->where(fn ($query) => $query
                        ->where('asset_type_id', $assetTypeId)
                        ->whereNull('deleted_at')),
            ],
            'asset_name' => ['required', 'string', 'max:255'],
            'account_group' => ['nullable', 'string', 'max:255'],
        ];
    }
}
