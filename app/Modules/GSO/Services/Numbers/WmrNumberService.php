<?php

namespace App\Modules\GSO\Services\Numbers;

use App\Models\DocumentNumberCounter;
use App\Modules\GSO\Services\Contracts\Numbers\WmrNumberServiceInterface;
use Illuminate\Support\Facades\DB;

class WmrNumberService implements WmrNumberServiceInterface
{
    public function nextNumber(?\DateTimeInterface $date = null): string
    {
        $date = $date ?: now();
        $year = (int) $date->format('Y');
        $documentType = 'wmr';

        return DB::transaction(function () use ($year, $documentType) {
            $counter = DocumentNumberCounter::query()
                ->where('document_type', $documentType)
                ->where('year', $year)
                ->whereNull('period_key')
                ->whereNull('scope_key')
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                $counter = DocumentNumberCounter::query()->create([
                    'document_type' => $documentType,
                    'year' => $year,
                    'period_key' => null,
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

            return sprintf('WMR-%d-%06d', $year, $next);
        });
    }
}
