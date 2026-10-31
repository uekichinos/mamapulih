<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

/**
 * Backs up the database and private storage (receipts, refund proofs,
 * payment QR uploads) into one timestamped .tar.gz, so a restore doesn't
 * need two separate files kept in sync.
 */
class BackupCommand extends Command
{
    protected $signature = 'mamapulih:backup {--path= : Directory to write the backup into (defaults to storage/app/backups)}';

    protected $description = 'Back up the database and private storage into a single archive';

    public function handle(): int
    {
        $path = $this->option('path');
        $backupDir = is_string($path) && $path !== '' ? $path : storage_path('app/backups');
        File::ensureDirectoryExists($backupDir);

        $timestamp = now()->format('Y-m-d_His');
        $workDir = storage_path("app/backups/tmp_{$timestamp}");
        File::ensureDirectoryExists($workDir);

        $this->components->task('Dumping database', function () use ($workDir) {
            $dumpPath = "{$workDir}/database.sql";

            $process = new Process([
                'pg_dump',
                '--host='.config('database.connections.pgsql.host'),
                '--port='.config('database.connections.pgsql.port'),
                '--username='.config('database.connections.pgsql.username'),
                '--no-password',
                '--format=plain',
                '--file='.$dumpPath,
                config('database.connections.pgsql.database'),
            ]);
            $process->setEnv(['PGPASSWORD' => (string) config('database.connections.pgsql.password')]);
            $process->setTimeout(null);
            $process->run();

            return $process->isSuccessful();
        });

        $privateStoragePath = Storage::disk('local')->path('');

        $this->components->task('Copying private storage', function () use ($privateStoragePath, $workDir) {
            if (File::isDirectory($privateStoragePath)) {
                File::copyDirectory($privateStoragePath, "{$workDir}/storage");
            }

            return true;
        });

        $archivePath = "{$backupDir}/mamapulih-backup-{$timestamp}.tar.gz";

        $this->components->task('Creating archive', function () use ($workDir, $archivePath) {
            $process = new Process(['tar', '-czf', $archivePath, '-C', $workDir, '.']);
            $process->setTimeout(null);
            $process->run();

            return $process->isSuccessful();
        });

        File::deleteDirectory($workDir);

        $this->components->info("Backup written to {$archivePath}");

        return self::SUCCESS;
    }
}
