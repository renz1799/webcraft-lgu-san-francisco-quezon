<?php

namespace App\Modules\GSO\Http\Requests\AssetTypes;

use App\Http\Requests\BaseFormRequest;
use App\Modules\GSO\Http\Requests\Concerns\AuthorizesGsoPermissions;
use Illuminate\Validation\Rule;

class UpdateAssetTypeRequest extends BaseFormRequest
{
    use AuthorizesGsoPermissions;

    public function authorize(): bool
    {
        return $this->allowsGsoPermission('asset_types.update');
    }

    public function rules(): array
    {
        $assetTypeId = (string) $this->route('assetType');

        return [
            'type_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('asset_types', 'type_code')->ignore($assetTypeId, 'id'),
            ],
            'type_name' => ['required', 'string', 'max:255'],
        ];
    }
}
