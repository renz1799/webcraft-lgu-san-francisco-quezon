<?php

namespace App\Modules\GSO\Http\Requests\Air;

use App\Modules\GSO\Http\Requests\Air\Concerns\ValidatesConfiguredAirItemUnits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateAirItemRequest extends FormRequest
{
    use ValidatesConfiguredAirItemUnits;

    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsPermission($user, 'air.manage_items');
    }

    public function rules(): array
    {
        return [
            'description_snapshot' => ['nullable', 'string', 'max:5000'],
            'unit_snapshot' => ['nullable', 'string', 'max:50'],
            'qty_ordered' => ['nullable', 'integer', 'min:1'],
            'acquisition_cost' => ['nullable', 'numeric', 'min:0'],
            'remarks' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function (Validator $validator): void {
            $routeAir = $this->route('air');
            $routeAirItem = $this->route('airItem');

            $airId = is_object($routeAir) ? (string) ($routeAir->id ?? '') : (string) $routeAir;
            $airItemId = is_object($routeAirItem) ? (string) ($routeAirItem->id ?? '') : (string) $routeAirItem;
            $airItem = $this->findAirItemForUnitValidation($airItemId, $airId);
            $item = $airItem?->item;

            if (! $item) {
                return;
            }

            $selectedUnit = array_key_exists('unit_snapshot', $this->all())
                ? $this->input('unit_snapshot')
                : $airItem->unit_snapshot;

            $this->validateConfiguredUnitSelection($validator, 'unit_snapshot', $item, $selectedUnit);
        });
    }
}
