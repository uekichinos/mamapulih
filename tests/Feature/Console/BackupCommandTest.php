<?php

it('writes a timestamped backup archive', function () {
    $path = storage_path('app/backups/test-'.uniqid());

    $this->artisan('mamapulih:backup', ['--path' => $path])
        ->assertExitCode(0);

    $files = glob("{$path}/mamapulih-backup-*.tar.gz");

    expect($files)->not->toBeEmpty();

    array_map('unlink', $files);
    rmdir($path);
});
