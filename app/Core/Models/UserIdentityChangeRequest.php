<?php

namespace App\Core\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserIdentityChangeRequest extends Model
{
    use HasUuids;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'current_first_name',
        'current_last_name',
        'current_middle_name',
        'current_name_extension',
        'requested_first_name',
        'requested_last_name',
        'requested_middle_name',
        'requested_name_extension',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'id');
    }

    public function isPending(): bool
    {
        return (string) $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return (string) $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return (string) $this->status === self::STATUS_REJECTED;
    }

    public function currentFullName(): string
    {
        return $this->composeName(
            $this->current_first_name,
            $this->current_middle_name,
            $this->current_last_name,
            $this->current_name_extension
        );
    }

    public function requestedFullName(): string
    {
        return $this->composeName(
            $this->requested_first_name,
            $this->requested_middle_name,
            $this->requested_last_name,
            $this->requested_name_extension
        );
    }

    private function composeName(
        ?string $firstName,
        ?string $middleName,
        ?string $lastName,
        ?string $nameExtension
    ): string {
        return collect([
            trim((string) $firstName),
            trim((string) $middleName),
            trim((string) $lastName),
            trim((string) $nameExtension),
        ])->filter()->implode(' ');
    }
}
