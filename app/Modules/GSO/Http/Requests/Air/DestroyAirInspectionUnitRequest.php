<?php

namespace App\Modules\GSO\Http\Requests\Air;

use App\Modules\GSO\Services\Air\AirInspectionWorkspaceAccessService;
use Illuminate\Foundation\Http\FormRequest;

class DestroyAirInspectionUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return app(AirInspectionWorkspaceAccessService::class)->canManage(
            $this->user(),
            (string) $this->route('air'),
        );
    }

    public function rules(): array
    {
        return [];
    }
}
