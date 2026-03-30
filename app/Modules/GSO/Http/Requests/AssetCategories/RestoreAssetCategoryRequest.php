<?php

namespace App\Modules\GSO\Http\Requests\AssetCategories;

use App\Http\Requests\BaseFormRequest;
use App\Modules\GSO\Http\Requests\Concerns\AuthorizesGsoPermissions;

class RestoreAssetCategoryRequest extends BaseFormRequest
{
    use AuthorizesGsoPermissions;

    public function authorize(): bool
    {
        return $this->allowsGsoPermission('asset_categories.restore');
    }

    public function rules(): array
    {
        return [];
    }
}
