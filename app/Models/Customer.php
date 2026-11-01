<?php

namespace App\Models;

use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @use HasFactory<CustomerFactory>
 */
class Customer extends Authenticatable
{
    /** @use HasFactory<CustomerFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'preferred_locale',
        'email_verified_at',
        'do_not_contact',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'do_not_contact' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * @return HasMany<Address, $this>
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * @return HasMany<Booking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Every booking that hasn't reached an inactive, terminal status —
     * plural, since one account can have more than one live booking at
     * once (e.g. two wives expecting at the same time, see dev plan P42).
     *
     * @return HasMany<Booking, $this>
     */
    public function liveBookings(): HasMany
    {
        return $this->bookings()->live()->latest()->latest('id');
    }

    /**
     * Every terminal-status booking — always historical (see dev plan P20).
     *
     * @return HasMany<Booking, $this>
     */
    public function archivedBookings(): HasMany
    {
        return $this->bookings()->terminal()->latest()->latest('id');
    }
}
