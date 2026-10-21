<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ResetTwoFactorCommand extends Command
{
    protected $signature = 'mamapulih:reset-2fa {email}';

    protected $description = 'Turn off two-factor authentication for a staff account from the server, e.g. when the Owner has lost their authenticator device';

    public function handle(): int
    {
        $email = $this->argument('email');

        if (! is_string($email)) {
            $this->components->error('Please provide a single email address.');

            return self::FAILURE;
        }

        $user = User::query()->where('email', $email)->first();

        if (! $user) {
            $this->components->error("No staff account found for {$email}.");

            return self::FAILURE;
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $this->components->info("Two-factor authentication has been turned off for {$user->email}.");

        if ($user->isOwner()) {
            $this->components->warn('They will be asked to set it up again the next time they log in.');
        }

        return self::SUCCESS;
    }
}
