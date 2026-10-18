<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $installed_at
 * @property Carbon|null $setup_code_expires_at
 * @property Carbon|null $locked_until
 */
class SystemState extends Model
{
    protected $table = 'system_state';

    protected $fillable = [
        'installed_at',
        'installed_by',
        'setup_code_hash',
        'setup_code_expires_at',
        'failed_attempts',
        'locked_until',
        'app_version',
    ];

    protected function casts(): array
    {
        return [
            'installed_at' => 'datetime',
            'setup_code_expires_at' => 'datetime',
            'locked_until' => 'datetime',
        ];
    }

    public static function current(): self
    {
        return self::query()->firstOrCreate([]);
    }

    public function isInstalled(): bool
    {
        return ! is_null($this->installed_at);
    }

    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }
}
