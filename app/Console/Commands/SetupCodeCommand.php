<?php

namespace App\Console\Commands;

use App\Domain\Setup\InstallationService;
use Illuminate\Console\Command;

class SetupCodeCommand extends Command
{
    protected $signature = 'mamapulih:setup-code';

    protected $description = 'Print a fresh installer setup code (only while the app is not yet installed)';

    public function handle(InstallationService $installer): int
    {
        if ($installer->isInstalled()) {
            $this->components->error('MamaPulih is already installed. The setup code can no longer be used.');

            return self::FAILURE;
        }

        $code = $installer->generateSetupCode();

        $this->components->info("Setup code: {$code}");

        return self::SUCCESS;
    }
}
