<?php

namespace App\Modules\GSO\Http\Requests\AssetTypes;

use App\Http\Requests\BaseFormRequest;
use App\Modules\GSO\Http\Requests\Concerns\AuthorizesGsoPermissions;

class StoreAssetTypeRequest extends BaseFormRequest
{
    use AuthorizesGsoPermissions;

    public function authorize(): bool
    {
        return $this->allowsGsoPermission('asset_types.create');
    }

    public function rules(): array
    {
        return [
            'type_code' => ['required', 'string', 'max:50', 'unique:asset_types,type_code'],
            'type_name' => ['required', 'string', 'max:255'],
        ];
    }
}
