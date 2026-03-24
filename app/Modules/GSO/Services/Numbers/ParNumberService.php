<?php

namespace App\Services\Numbers;

use App\Models\DocumentNumberCounter;
use App\Services\Contracts\ParNumberServiceInterface;
use Illuminate\Support\Facades\DB;

class ParNumberService implements ParNumberServiceInterface
{
    /**
     * Format:
     * PAR-YYYY-000001
     */
    public function nextNumber(?\DateTimeInterface $date = null): string
    {
        $date = $date ?: now();

        $year = (int) $date->format('Y');
        $documentType = 'par';

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

                // ensure the row is locked within this tx before increment
                $counter = DocumentNumberCounter::query()
                    ->whereKey($counter->id)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            $next = ((int) $counter->last_seq) + 1;

            $counter->last_seq = $next;
            $counter->save();

            return sprintf('PAR-%d-%06d', $year, $next);
        });
    }
}