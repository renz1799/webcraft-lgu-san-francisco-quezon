<?php

namespace App\Modules\GSO\Http\Requests\AssetTypes;

use App\Http\Requests\BaseFormRequest;

class StoreAssetTypeRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user
            && ($user->hasAnyRole(['Administrator', 'admin']) || $user->can('modify Asset Types'));
    }

    public function rules(): array
    {
        return [
            'type_code' => ['required', 'string', 'max:50', 'unique:asset_types,type_code'],
            'type_name' => ['required', 'string', 'max:255'],
        ];
    }
}
