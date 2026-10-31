<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

/**
 * Restores a backup created by `mamapulih:backup`. Destructive — overwrites
 * the current database and private storage — so it asks for confirmation
 * unless --force is passed.
 */
class RestoreCommand extends Command
{
    protected $signature = 'mamapulih:restore {file : Path to the .tar.gz backup archive} {--force : Skip the confirmation prompt}';

    protected $description = 'Restore the database and private storage from a backup archive';

    public function handle(): int
    {
        $archivePath = $this->argument('file');

        if (! File::exists($archivePath)) {
            $this->components->error("Backup archive not found: {$archivePath}");

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('This will overwrite the current database and private storage. Continue?')) {
            $this->components->warn('Restore cancelled.');

            return self::SUCCESS;
        }

        $workDir = storage_path('app/backups/tmp_restore_'.now()->format('Y-m-d_His'));
        File::ensureDirectoryExists($workDir);

        $this->components->task('Extracting archive', function () use ($archivePath, $workDir) {
            $process = new Process(['tar', '-xzf', $archivePath, '-C', $workDir]);
            $process->setTimeout(null);
            $process->run();

            return $process->isSuccessful();
        });

        $dumpPath = "{$workDir}/database.sql";

        if (! File::exists($dumpPath)) {
            $this->components->error('Archive does not contain a database.sql — not a valid backup.');
            File::deleteDirectory($workDir);

            return self::FAILURE;
        }

        $this->components->task('Restoring database', function () use ($dumpPath) {
            $process = new Process([
                'psql',
                '--host='.config('database.connections.pgsql.host'),
                '--port='.config('database.connections.pgsql.port'),
                '--username='.config('database.connections.pgsql.username'),
                '--no-password',
                '--file='.$dumpPath,
                config('database.connections.pgsql.database'),
            ]);
            $process->setEnv(['PGPASSWORD' => (string) config('database.connections.pgsql.password')]);
            $process->setTimeout(null);
            $process->run();

            return $process->isSuccessful();
        });

        $storageBackupPath = "{$workDir}/storage";

        $this->components->task('Restoring private storage', function () use ($storageBackupPath) {
            if (File::isDirectory($storageBackupPath)) {
                File::copyDirectory($storageBackupPath, Storage::disk('local')->path(''));
            }

            return true;
        });

        File::deleteDirectory($workDir);

        $this->components->info('Restore complete.');

        return self::SUCCESS;
    }
}
