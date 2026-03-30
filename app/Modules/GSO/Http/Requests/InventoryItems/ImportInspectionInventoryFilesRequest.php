<?php

namespace App\Modules\GSO\Http\Requests\InventoryItems;

use App\Modules\GSO\Http\Requests\Concerns\AuthorizesGsoPermissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImportInspectionInventoryFilesRequest extends FormRequest
{
    use AuthorizesGsoPermissions;

    public function authorize(): bool
    {
        return $this->allowsGsoPermission('inventory_items.import_from_inspection');
    }

    public function rules(): array
    {
        return [
            'inspection_id' => [
                'required',
                'uuid',
                Rule::exists('inspections', 'id')->where(fn ($query) => $query->whereNull('deleted_at')),
            ],
        ];
    }
}
