<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    protected $fillable = ['booking_day_id', 'run', 'includes_dinner', 'zone_id', 'transport_cost_sen', 'status'];

    protected function casts(): array
    {
        return [
            'includes_dinner' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<BookingDay, $this>
     */
    public function bookingDay(): BelongsTo
    {
        return $this->belongsTo(BookingDay::class);
    }

    /**
     * @return BelongsTo<Zone, $this>
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }
}
