<?php

namespace App\Domain\Settings;

use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class SettingsManager
{
    /**
     * Get the current value for a setting key, or $default if it has never been set.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $setting = Setting::query()
            ->where('key', $key)
            ->orderByDesc('version')
            ->first();

        return is_null($setting) ? $default : $setting->value;
    }

    /**
     * Write a new version of a setting. Settings are append-only: every
     * change creates a new version row so bookings can snapshot the rules
     * in force when they were made without being affected by later changes.
     *
     * @param  array<string, mixed>  $value
     */
    public function set(string $key, array $value): Setting
    {
        return DB::transaction(function () use ($key, $value) {
            $nextVersion = (Setting::query()->where('key', $key)->max('version') ?? 0) + 1;

            return Setting::query()->create([
                'key' => $key,
                'value' => $value,
                'version' => $nextVersion,
                'effective_from' => now(),
            ]);
        });
    }

    /**
     * Snapshot the current values of several keys, e.g. for a booking's
     * policy_snapshot column.
     *
     * @param  array<int, string>  $keys
     * @return array<string, mixed>
     */
    public function snapshot(array $keys): array
    {
        return collect($keys)->mapWithKeys(fn (string $key) => [$key => $this->get($key)])->all();
    }
}
