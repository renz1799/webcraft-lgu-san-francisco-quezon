<?php

namespace App\Services\Numbers;

use App\Models\DocumentNumberCounter;
use App\Services\Contracts\PropertyNumberServiceInterface;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class PropertyNumberService implements PropertyNumberServiceInterface
{
    /**
     * Canonical generator.
     *
     * Counter scope:
     * - document_type = property / property_ics
     * - year          = acquisition year
     * - period_key    = null
     * - scope_key     = asset code
     *
     * Output format:
     *   2026-1-04-04-010-ICS-00001
     *   2026-1-04-04-010-PPE-00001
     */
    public function generate(CarbonInterface $acquisitionDate, string $assetCode, bool $isIcs): string
    {
        $year = (int) $acquisitionDate->format('Y');
        $assetCode = trim((string) $assetCode);

        if ($assetCode === '') {
            throw new \InvalidArgumentException('assetCode is required to generate property number.');
        }

        $documentType = $isIcs ? 'property_ics' : 'property';
        $prefixType = $isIcs ? 'ICS' : 'PPE';

        return DB::transaction(function () use ($year, $assetCode, $documentType, $prefixType) {
            $counter = DocumentNumberCounter::query()
                ->where('document_type', $documentType)
                ->where('year', $year)
                ->whereNull('period_key')
                ->where('scope_key', $assetCode)
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                $counter = DocumentNumberCounter::query()->create([
                    'document_type' => $documentType,
                    'year' => $year,
                    'period_key' => null,
                    'scope_key' => $assetCode,
                    'last_seq' => 0,
                ]);

                $counter = DocumentNumberCounter::query()
                    ->whereKey($counter->id)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            $next = ((int) $counter->last_seq) + 1;

            $counter->last_seq = $next;
            $counter->save();

            $seq = str_pad((string) $next, 5, '0', STR_PAD_LEFT);

            return "{$year}-{$assetCode}-{$prefixType}-{$seq}";
        });
    }

    /**
     * Convenience method used by other services (promotion, etc.).
     * Uses acquisitionDate's year if provided; otherwise current year.
     */
    public function nextForAssetCode(string $assetCode, bool $isIcs, ?string $acquisitionDate = null): string
    {
        $assetCode = trim((string) $assetCode);

        if ($assetCode === '') {
            throw new \InvalidArgumentException('assetCode is required.');
        }

        $dt = null;

        if (!empty($acquisitionDate)) {
            try {
                $dt = Carbon::parse($acquisitionDate);
            } catch (\Throwable $e) {
                $dt = null;
            }
        }

        $dt = $dt ?: now();

        return $this->generate($dt, $assetCode, $isIcs);
    }
}