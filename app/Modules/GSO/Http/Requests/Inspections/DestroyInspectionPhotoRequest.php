<?php

namespace App\Modules\GSO\Http\Requests\Inspections;

use App\Modules\GSO\Http\Requests\Concerns\AuthorizesGsoPermissions;
use Illuminate\Foundation\Http\FormRequest;

class DestroyInspectionPhotoRequest extends FormRequest
{
    use AuthorizesGsoPermissions;

    public function authorize(): bool
    {
        return $this->allowsGsoPermission('inspections.manage_photos');
    }

    public function rules(): array
    {
        return [];
    }
}
