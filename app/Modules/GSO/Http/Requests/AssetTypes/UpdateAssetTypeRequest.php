<?php

namespace App\Modules\GSO\Http\Requests\AssetTypes;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class UpdateAssetTypeRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user
            && ($user->hasAnyRole(['Administrator', 'admin']) || $user->can('modify Asset Types'));
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
