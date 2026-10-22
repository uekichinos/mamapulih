<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PlanChange extends Model
{
    protected $fillable = [
        'booking_id',
        'from_variant_id',
        'to_variant_id',
        'effective_date',
        'remaining_days',
        'amount_sen',
        'override_reason',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Booking, $this>
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * @return BelongsTo<Variant, $this>
     */
    public function fromVariant(): BelongsTo
    {
        return $this->belongsTo(Variant::class, 'from_variant_id');
    }

    /**
     * @return BelongsTo<Variant, $this>
     */
    public function toVariant(): BelongsTo
    {
        return $this->belongsTo(Variant::class, 'to_variant_id');
    }

    /**
     * @return HasOne<Consent, $this>
     */
    public function consent(): HasOne
    {
        return $this->hasOne(Consent::class);
    }

    public function isUpgrade(): bool
    {
        return $this->amount_sen > 0;
    }
}
