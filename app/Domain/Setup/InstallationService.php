<?php

namespace App\Domain\Setup;

use App\Enums\UserRole;
use App\Models\SystemState;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InstallationService
{
    private const SETUP_CODE_TTL_HOURS = 24;

    private const MAX_ATTEMPTS = 5;

    private const LOCKOUT_MINUTES = 15;

    public function isInstalled(): bool
    {
        return SystemState::current()->isInstalled();
    }

    /**
     * Generate a fresh setup code if none exists yet, or the current one
     * has expired. The plain code is only ever available via the log line
     * printed here — it is never retrievable from the database again.
     */
    public function ensureSetupCode(): void
    {
        $state = SystemState::current();

        if ($state->isInstalled()) {
            return;
        }

        if ($state->setup_code_hash && $state->setup_code_expires_at?->isFuture()) {
            return;
        }

        $this->generateSetupCode();
    }

    public function generateSetupCode(): string
    {
        $code = Str::upper(Str::random(4).'-'.Str::random(4));

        SystemState::current()->update([
            'setup_code_hash' => Hash::make($code),
            'setup_code_expires_at' => now()->addHours(self::SETUP_CODE_TTL_HOURS),
            'failed_attempts' => 0,
            'locked_until' => null,
        ]);

        Log::info("MamaPulih setup code: {$code} (expires in ".self::SETUP_CODE_TTL_HOURS.'h)');

        return $code;
    }

    public function verifyCode(string $code): bool
    {
        $state = SystemState::current();

        if ($state->isLocked()) {
            return false;
        }

        $valid = $state->setup_code_hash
            && $state->setup_code_expires_at?->isFuture()
            && Hash::check($code, $state->setup_code_hash);

        if ($valid) {
            $state->update(['failed_attempts' => 0, 'locked_until' => null]);

            return true;
        }

        $attempts = $state->failed_attempts + 1;
        $state->update([
            'failed_attempts' => $attempts,
            'locked_until' => $attempts >= self::MAX_ATTEMPTS
                ? now()->addMinutes(self::LOCKOUT_MINUTES)
                : null,
        ]);

        return false;
    }

    /**
     * @return array<int, array{name: string, pass: bool, message: string}>
     */
    public function checkEnvironment(): array
    {
        $checks = [];

        $checks[] = [
            'name' => 'PHP version',
            'pass' => version_compare(PHP_VERSION, '8.3.0', '>='),
            'message' => 'PHP '.PHP_VERSION,
        ];

        foreach (['pdo_pgsql', 'mbstring', 'openssl', 'bcmath', 'intl'] as $extension) {
            $checks[] = [
                'name' => "Extension: {$extension}",
                'pass' => extension_loaded($extension),
                'message' => extension_loaded($extension) ? 'Loaded' : 'Missing',
            ];
        }

        $checks[] = [
            'name' => 'Application key',
            'pass' => (bool) config('app.key'),
            'message' => config('app.key') ? 'Set' : 'Not set',
        ];

        $checks[] = [
            'name' => 'Storage writable',
            'pass' => is_writable(storage_path()),
            'message' => storage_path(),
        ];

        $dbOk = true;
        $dbMessage = 'Connected';

        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $dbOk = false;
            $dbMessage = $e->getMessage();
        }

        $checks[] = [
            'name' => 'Database reachable',
            'pass' => $dbOk,
            'message' => $dbMessage,
        ];

        return $checks;
    }

    public function environmentIsReady(): bool
    {
        return collect($this->checkEnvironment())->every(fn (array $check) => $check['pass']);
    }

    /**
     * @param  array{name: string, email: string, password: string}  $admin
     */
    public function complete(array $admin, bool $loadDemoData): User
    {
        return DB::transaction(function () use ($admin, $loadDemoData) {
            $owner = User::query()->create([
                'name' => $admin['name'],
                'email' => $admin['email'],
                'password' => $admin['password'],
                'role' => UserRole::Owner,
                'email_verified_at' => now(),
            ]);

            SystemState::current()->update([
                'installed_at' => now(),
                'installed_by' => $owner->id,
                'setup_code_hash' => null,
                'setup_code_expires_at' => null,
                'app_version' => config('app.version', 'dev'),
            ]);

            if ($loadDemoData) {
                Artisan::call('db:seed', ['--force' => true]);
            }

            return $owner;
        });
    }
}
