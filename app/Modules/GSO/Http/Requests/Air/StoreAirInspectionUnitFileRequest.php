<?php

namespace App\Modules\GSO\Http\Requests\Air;

use App\Modules\GSO\Support\InventoryFileTypes;
use App\Modules\GSO\Services\Air\AirInspectionWorkspaceAccessService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAirInspectionUnitFileRequest extends FormRequest
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
            'photos' => ['nullable', 'array', 'min:1'],
            'photos.*' => ['nullable', 'image', 'max:10240'],
            'files' => ['nullable', 'array', 'min:1'],
            'files.*' => ['nullable', 'image', 'max:10240'],
            'type' => ['nullable', 'string', Rule::in(InventoryFileTypes::airImageValues())],
            'caption' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $uploads = array_merge(
                    $this->file('photos', []),
                    $this->file('files', []),
                );

                if ($uploads === []) {
                    $validator->errors()->add('files', 'At least one image is required.');
                }
            },
        ];
    }
}
