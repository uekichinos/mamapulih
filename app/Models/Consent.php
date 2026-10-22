<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $expires_at
 * @property Carbon|null $decided_at
 */
class Consent extends Model
{
    protected $fillable = [
        'plan_change_id',
        'token_hash',
        'expires_at',
        'decision',
        'decided_at',
        'ip',
        'user_agent',
        'snapshot',
        'terms_version',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'decided_at' => 'datetime',
            'snapshot' => 'array',
        ];
    }

    /**
     * @return BelongsTo<PlanChange, $this>
     */
    public function planChange(): BelongsTo
    {
        return $this->belongsTo(PlanChange::class);
    }

    public function isExpired(): bool
    {
        return is_null($this->decision) && $this->expires_at->isPast();
    }

    public function isDecided(): bool
    {
        return ! is_null($this->decision);
    }
}
