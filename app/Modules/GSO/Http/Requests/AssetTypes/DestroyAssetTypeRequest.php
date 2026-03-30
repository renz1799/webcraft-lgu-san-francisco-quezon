<?php

namespace App\Modules\GSO\Http\Requests\AssetTypes;

use App\Http\Requests\BaseFormRequest;
use App\Modules\GSO\Http\Requests\Concerns\AuthorizesGsoPermissions;

class DestroyAssetTypeRequest extends BaseFormRequest
{
    use AuthorizesGsoPermissions;

    public function authorize(): bool
    {
        return $this->allowsGsoPermission('asset_types.archive');
    }

    public function rules(): array
    {
        return [];
    }
}
