<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ResetAdminPasswordCommand extends Command
{
    protected $signature = 'mamapulih:reset-admin-password {email}';

    protected $description = 'Reset a staff account password from the server, without going through the app';

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

        $password = Str::password(16);

        $user->forceFill(['password' => $password])->save();

        $this->components->info("Password for {$user->email} has been reset to:");
        $this->line($password);
        $this->components->warn('Share this with them securely and ask them to change it after logging in.');

        return self::SUCCESS;
    }
}
