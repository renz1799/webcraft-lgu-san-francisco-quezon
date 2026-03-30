<?php

namespace App\Modules\GSO\Http\Requests\Reports;

use Illuminate\Foundation\Http\FormRequest;

class PrintStickerReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return (bool) $user && app(\App\Core\Support\AdminContextAuthorizer::class)->allowsAnyPermission($user, [
            'reports.stickers.view',
            'inventory_items.view',
            'inventory_items.update',
        ]);
    }

    public function rules(): array
    {
        return [
            'preview' => ['nullable', 'boolean'],
            'inventory_item_id' => ['nullable', 'uuid', 'exists:inventory_items,id'],
            'inventory_item_ids' => ['nullable', 'array'],
            'inventory_item_ids.*' => ['uuid', 'exists:inventory_items,id'],
            'copies' => ['nullable', 'integer', 'min:1', 'max:24'],
            'show_cut_guides' => ['nullable', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $inventoryItemIds = collect($this->input('inventory_item_ids', []));

        if (! is_array($this->input('inventory_item_ids'))) {
            $inventoryItemIds = collect([$this->input('inventory_item_ids')]);
        }

        $inventoryItemIds = $inventoryItemIds
            ->flatten(1)
            ->map(function ($value) {
                if (! is_string($value)) {
                    return $value;
                }

                $clean = trim($value);

                return $clean !== '' ? $clean : null;
            })
            ->filter(static fn ($value) => filled($value))
            ->values();

        $legacyInventoryItemId = $this->nullableTrim('inventory_item_id');

        if ($legacyInventoryItemId !== null && ! $inventoryItemIds->contains($legacyInventoryItemId)) {
            $inventoryItemIds->prepend($legacyInventoryItemId);
        }

        $normalized = [
            'inventory_item_id' => $legacyInventoryItemId,
            'inventory_item_ids' => $inventoryItemIds->unique()->values()->all(),
            'preview' => $this->has('preview') ? $this->boolean('preview') : null,
            'show_cut_guides' => $this->has('show_cut_guides') ? $this->boolean('show_cut_guides') : null,
        ];

        $copies = $this->input('copies');

        if ($copies === '' || $copies === null) {
            $normalized['copies'] = null;
        } elseif (is_numeric($copies)) {
            $normalized['copies'] = (int) $copies;
        }

        $this->merge($normalized);
    }

    private function nullableTrim(string $key): ?string
    {
        $value = $this->input($key);

        if (! is_string($value)) {
            return $value;
        }

        $clean = trim($value);

        return $clean !== '' ? $clean : null;
    }
}
