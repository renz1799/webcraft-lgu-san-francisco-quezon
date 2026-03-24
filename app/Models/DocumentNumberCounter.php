<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DocumentNumberCounter extends Model
{
    protected $table = 'document_number_counters';

    protected $fillable = [
        'document_type',
        'year',
        'period_key',
        'scope_key',
        'last_seq',
    ];

    protected $casts = [
        'year' => 'integer',
        'last_seq' => 'integer',
    ];

    public function scopeForType(Builder $query, string $documentType): Builder
    {
        return $query->where('document_type', $documentType);
    }

    public function scopeForYear(Builder $query, int $year): Builder
    {
        return $query->where('year', $year);
    }

    public function scopeForPeriod(Builder $query, ?string $periodKey): Builder
    {
        return is_null($periodKey)
            ? $query->whereNull('period_key')
            : $query->where('period_key', $periodKey);
    }

    public function scopeForScope(Builder $query, ?string $scopeKey): Builder
    {
        return is_null($scopeKey)
            ? $query->whereNull('scope_key')
            : $query->where('scope_key', $scopeKey);
    }
}
