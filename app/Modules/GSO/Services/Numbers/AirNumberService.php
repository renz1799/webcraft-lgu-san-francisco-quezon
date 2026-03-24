<?php

namespace App\Services\Numbers;

use App\Models\DocumentNumberCounter;
use App\Services\Contracts\AirNumberServiceInterface;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class AirNumberService implements AirNumberServiceInterface
{
    /**
     * Format:
     * AIR-YYYY-MM-####
     *
     * Counter scope:
     * - document_type = air
     * - year          = YYYY
     * - period_key    = YYYY-MM
     * - scope_key     = null
     */
    public function generate(?CarbonInterface $date = null): string
    {
        $date = $date ? Carbon::instance($date) : now();

        $year = (int) $date->format('Y');
        $month = $date->format('m');
        $periodKey = $date->format('Y-m');
        $documentType = 'air';

        return DB::transaction(function () use ($documentType, $year, $month, $periodKey) {
            $counter = DocumentNumberCounter::query()
                ->where('document_type', $documentType)
                ->where('year', $year)
                ->where('period_key', $periodKey)
                ->whereNull('scope_key')
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                $counter = DocumentNumberCounter::query()->create([
                    'document_type' => $documentType,
                    'year' => $year,
                    'period_key' => $periodKey,
                    'scope_key' => null,
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

            $seq = str_pad((string) $next, 4, '0', STR_PAD_LEFT);

            return "AIR-{$year}-{$month}-{$seq}";
        });
    }
}