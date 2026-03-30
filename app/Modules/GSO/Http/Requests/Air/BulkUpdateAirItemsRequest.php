<?php

namespace App\Modules\GSO\Http\Requests\Air;

use App\Modules\GSO\Http\Requests\Air\Concerns\ValidatesConfiguredAirItemUnits;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class BulkUpdateAirItemsRequest extends FormRequest
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
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'uuid'],
            'items.*.description_snapshot' => ['nullable', 'string', 'max:5000'],
            'items.*.unit_snapshot' => ['nullable', 'string', 'max:50'],
            'items.*.qty_ordered' => ['nullable', 'integer', 'min:1'],
            'items.*.acquisition_cost' => ['nullable', 'numeric', 'min:0'],
            'items.*.remarks' => ['nullable', 'string', 'max:5000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function (Validator $validator): void {
            $routeAir = $this->route('air');
            $airId = is_object($routeAir) ? (string) ($routeAir->id ?? '') : (string) $routeAir;
            $rows = $this->input('items', []);

            if (! is_array($rows)) {
                return;
            }

            foreach ($rows as $index => $row) {
                if (! is_array($row)) {
                    continue;
                }

                $airItem = $this->findAirItemForUnitValidation((string) ($row['id'] ?? ''), $airId);
                $item = $airItem?->item;

                if (! $item) {
                    continue;
                }

                $selectedUnit = array_key_exists('unit_snapshot', $row)
                    ? ($row['unit_snapshot'] ?? null)
                    : $airItem->unit_snapshot;

                $this->validateConfiguredUnitSelection(
                    $validator,
                    "items.{$index}.unit_snapshot",
                    $item,
                    $selectedUnit
                );
            }
        });
    }
}
