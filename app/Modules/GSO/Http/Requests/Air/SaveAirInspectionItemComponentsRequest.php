<?php

namespace App\Modules\GSO\Http\Requests\Air;

use App\Modules\GSO\Services\Air\AirInspectionWorkspaceAccessService;
use Illuminate\Foundation\Http\FormRequest;

class SaveAirInspectionItemComponentsRequest extends FormRequest
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
        return [
            'components' => ['required', 'array'],
            'components.*.id' => ['nullable', 'uuid'],
            'components.*.client_request_id' => ['nullable', 'uuid'],
            'components.*.name' => ['nullable', 'string', 'max:255'],
            'components.*.quantity' => ['nullable', 'integer', 'min:1'],
            'components.*.unit' => ['nullable', 'string', 'max:50'],
            'components.*.component_cost' => ['nullable', 'numeric'],
            'components.*.serial_number' => ['nullable', 'string', 'max:255'],
            'components.*.condition' => ['nullable', 'string', 'max:255'],
            'components.*.is_present' => ['nullable', 'boolean'],
            'components.*.remarks' => ['nullable', 'string', 'max:5000'],
        ];
    }
}
