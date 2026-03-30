<?php

namespace App\Modules\GSO\Http\Requests\AssetCategories;

use App\Http\Requests\BaseFormRequest;
use App\Modules\GSO\Http\Requests\Concerns\AuthorizesGsoPermissions;

class AssetCategoryTableDataRequest extends BaseFormRequest
{
    use AuthorizesGsoPermissions;

    public function authorize(): bool
    {
        return $this->allowsAnyGsoPermission([
            'asset_categories.view',
            'asset_categories.create',
            'asset_categories.update',
            'asset_categories.archive',
            'asset_categories.restore',
        ]);
    }

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'size' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'string', 'max:200'],
            'q' => ['nullable', 'string', 'max:200'],
            'archived' => ['nullable', 'in:active,archived,all'],
            'asset_type_id' => ['nullable', 'uuid'],
        ];
    }

    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (! is_array($data)) {
            return $data;
        }

        $data['page'] = (int) ($data['page'] ?? 1);
        $data['size'] = (int) ($data['size'] ?? 15);
        $data['archived'] = $data['archived'] ?? 'active';
        $data['search'] = (string) ($data['search'] ?? $data['q'] ?? '');

        return $data;
    }
}
