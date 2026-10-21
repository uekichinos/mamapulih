<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DietProfile extends Model
{
    protected $fillable = ['customer_id', 'allergies', 'exclusions', 'notes'];

    protected function casts(): array
    {
        return [
            'allergies' => 'array',
            'exclusions' => 'array',
            'notes' => 'encrypted',
        ];
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
