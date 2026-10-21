<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database. The Owner account is created by the
     * installer itself (App\Domain\Setup\InstallationService::complete),
     * so this only loads the "Load demo data" catalogue the installer
     * offers.
     */
    public function run(): void
    {
        $this->call(DemoDataSeeder::class);
    }
}
