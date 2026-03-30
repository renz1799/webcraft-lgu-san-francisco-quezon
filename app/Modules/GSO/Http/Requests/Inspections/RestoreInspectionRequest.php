<?php

namespace App\Modules\GSO\Http\Requests\Inspections;

use App\Modules\GSO\Http\Requests\Concerns\AuthorizesGsoPermissions;
use Illuminate\Foundation\Http\FormRequest;

class RestoreInspectionRequest extends FormRequest
{
    use AuthorizesGsoPermissions;

    public function authorize(): bool
    {
        return $this->allowsGsoPermission('inspections.restore');
    }

    public function rules(): array
    {
        return [];
    }
}
