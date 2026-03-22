<?php

namespace App\Modules\GSO\Http\Requests\InventoryItems;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImportInspectionInventoryFilesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasAnyRole(['Administrator', 'admin'])
            || $this->user()?->can('modify Inventory Items');
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
