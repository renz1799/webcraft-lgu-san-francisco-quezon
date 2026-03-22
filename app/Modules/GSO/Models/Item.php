<?php

namespace App\Modules\GSO\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Item extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'items';

    protected $fillable = [
        'asset_id',
        'item_name',
        'description',
        'base_unit',
        'item_identification',
        'major_sub_account_group',
        'tracking_type',
        'requires_serial',
        'is_semi_expendable',
        'is_selected',
    ];

    protected function casts(): array
    {
        return [
            'requires_serial' => 'boolean',
            'is_semi_expendable' => 'boolean',
            'is_selected' => 'boolean',
        ];
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'asset_id', 'id');
    }

    public function unitConversions(): HasMany
    {
        return $this->hasMany(ItemUnitConversion::class, 'item_id', 'id')
            ->orderBy('from_unit');
    }

    public function componentTemplates(): HasMany
    {
        return $this->hasMany(ItemComponentTemplate::class, 'item_id', 'id')
            ->orderBy('line_no')
            ->orderBy('name');
    }

    public function stocks(): HasMany
    {
        return $this->hasMany(Stock::class, 'item_id', 'id');
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'item_id', 'id');
    }

    /**
     * @return array<int, array{value: string, label: string, is_base: bool, multiplier: int}>
     */
    public function getAvailableUnitOptions(): array
    {
        $baseUnit = trim((string) ($this->base_unit ?? ''));
        $options = [];
        $seen = [];

        $pushOption = static function (string $value, string $label, bool $isBase, int $multiplier) use (&$options, &$seen): void {
            $normalized = Str::lower(trim($value));

            if ($normalized === '' || isset($seen[$normalized])) {
                return;
            }

            $seen[$normalized] = true;
            $options[] = [
                'value' => trim($value),
                'label' => trim($label),
                'is_base' => $isBase,
                'multiplier' => max(1, $multiplier),
            ];
        };

        if ($baseUnit !== '') {
            $pushOption($baseUnit, $baseUnit, true, 1);
        }

        $conversions = $this->relationLoaded('unitConversions')
            ? $this->unitConversions
            : $this->unitConversions()->get(['from_unit', 'multiplier']);

        foreach ($conversions as $conversion) {
            $fromUnit = trim((string) ($conversion->from_unit ?? ''));

            if ($fromUnit === '') {
                continue;
            }

            $multiplier = max(1, (int) ($conversion->multiplier ?? 1));
            $label = $baseUnit !== ''
                ? "{$fromUnit} (1 = {$multiplier} {$baseUnit})"
                : $fromUnit;

            $pushOption($fromUnit, $label, false, $multiplier);
        }

        return $options;
    }

    /**
     * @return array<int, string>
     */
    public function getAvailableUnitValues(): array
    {
        return array_map(
            static fn (array $option): string => (string) ($option['value'] ?? ''),
            $this->getAvailableUnitOptions()
        );
    }

    public function acceptsUnit(?string $unit): bool
    {
        return $this->canonicalUnitValue($unit) !== null;
    }

    public function canonicalUnitValue(?string $unit): ?string
    {
        $normalized = Str::lower(trim((string) ($unit ?? '')));

        if ($normalized === '') {
            return null;
        }

        foreach ($this->getAvailableUnitOptions() as $option) {
            $value = trim((string) ($option['value'] ?? ''));

            if ($value !== '' && Str::lower($value) === $normalized) {
                return $value;
            }
        }

        return null;
    }

    public function hasComponentTemplates(): bool
    {
        if ($this->relationLoaded('componentTemplates')) {
            return $this->componentTemplates->isNotEmpty();
        }

        return $this->componentTemplates()->exists();
    }
}
