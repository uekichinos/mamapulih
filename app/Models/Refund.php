<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    protected $fillable = [
        'booking_id',
        'plan_change_id',
        'reason',
        'amount_sen',
        'method',
        'reference',
        'proof_path',
        'status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
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
     * @return BelongsTo<PlanChange, $this>
     */
    public function planChange(): BelongsTo
    {
        return $this->belongsTo(PlanChange::class);
    }
}
