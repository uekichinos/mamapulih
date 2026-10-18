<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessProfile extends Model
{
    protected $table = 'business_profile';

    protected $fillable = [
        'name',
        'logo_path',
        'contact_phone',
        'email',
        'address',
        'timezone',
        'currency',
        'default_locale',
    ];

    public static function current(): self
    {
        return self::query()->firstOrCreate([]);
    }
}
