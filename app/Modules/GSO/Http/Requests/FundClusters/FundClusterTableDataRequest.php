<?php

namespace App\Modules\GSO\Http\Requests\FundClusters;

use App\Http\Requests\BaseFormRequest;
use App\Modules\GSO\Http\Requests\Concerns\AuthorizesGsoPermissions;

class FundClusterTableDataRequest extends BaseFormRequest
{
    use AuthorizesGsoPermissions;

    public function authorize(): bool
    {
        return $this->allowsAnyGsoPermission([
            'fund_clusters.view',
            'fund_clusters.create',
            'fund_clusters.update',
            'fund_clusters.archive',
            'fund_clusters.restore',
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
