<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property BookingStatus $status
 */
class Booking extends Model
{
    protected $fillable = [
        'ref_no',
        'customer_id',
        'variant_id',
        'address_id',
        'edd',
        'expected_birth_type',
        'duration_days',
        'meal_plan',
        'is_twins',
        'slots_used',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'price_sen',
        'daily_rate_sen',
        'policy_snapshot',
        'created_by',
        'projected_period',
    ];

    protected function casts(): array
    {
        return [
            'edd' => 'date',
            'is_twins' => 'boolean',
            'status' => BookingStatus::class,
            'approved_at' => 'datetime',
            'policy_snapshot' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<Variant, $this>
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }

    /**
     * @return BelongsTo<Address, $this>
     */
    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * @return HasMany<BookingAddon, $this>
     */
    public function addons(): HasMany
    {
        return $this->hasMany(BookingAddon::class);
    }
}
