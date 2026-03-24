<?php

namespace App\Modules\GSO\Services\Numbers;

use App\Models\DocumentNumberCounter;
use App\Modules\GSO\Models\Ris;
use App\Modules\GSO\Services\Contracts\Numbers\RisNumberServiceInterface;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class RisNumberService implements RisNumberServiceInterface
{
    public function generate(Ris $ris, ?CarbonInterface $date = null): string
    {
        $date = $date ? Carbon::instance($date) : now();

        $month = $date->format('m');
        $year = (int) $date->format('Y');
        $periodKey = $date->format('Y-m');
        $suffix = 'A';
        $prefix = $this->pickPrefix($ris);
        $scopeKey = "{$prefix}-{$suffix}";

        return DB::transaction(function () use ($month, $year, $periodKey, $suffix, $prefix, $scopeKey) {
            $counter = DocumentNumberCounter::query()
                ->where('document_type', 'ris')
                ->where('year', $year)
                ->where('period_key', $periodKey)
                ->where('scope_key', $scopeKey)
                ->lockForUpdate()
                ->first();

            if (!$counter) {
                $counter = DocumentNumberCounter::query()->create([
                    'document_type' => 'ris',
                    'year' => $year,
                    'period_key' => $periodKey,
                    'scope_key' => $scopeKey,
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

            return sprintf(
                '%s-%s-%s-%d-%s',
                $prefix,
                str_pad((string) $next, 4, '0', STR_PAD_LEFT),
                $month,
                $year,
                $suffix,
            );
        });
    }

    private function pickPrefix(Ris $ris): string
    {
        $candidates = [
            (string) ($ris->requesting_department_code_snapshot ?? ''),
            (string) ($ris->responsibility_center_code ?? ''),
            (string) ($ris->fpp_code ?? ''),
        ];

        foreach ($candidates as $candidate) {
            $candidate = trim($candidate);

            if ($candidate !== '' && preg_match('/^\d+$/', $candidate)) {
                return $candidate;
            }
        }

        return '00';
    }
}
