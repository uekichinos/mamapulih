# Backing up and restoring MamaPulih

A backup contains the full database and private storage (payment receipts, refund proofs, payment QR uploads) — everything needed to recreate the install from nothing but the backup archive and the Docker image.

## Taking a backup

```sh
docker compose -f docker-compose.prod.yml exec app php artisan mamapulih:backup
```

This writes a single `mamapulih-backup-<timestamp>.tar.gz` to `storage/app/backups` inside the container. Copy it out to somewhere durable (not just the same disk as the app):

```sh
docker compose -f docker-compose.prod.yml cp app:/var/www/html/storage/app/backups/mamapulih-backup-<timestamp>.tar.gz ./
```

Then upload that file off-server — object storage, another machine, wherever your usual backup destination is. MamaPulih doesn't upload backups anywhere itself; the file is yours to move.

## Scheduling backups

Run it on a cron job on the host, outside the container:

```cron
0 3 * * * cd /path/to/mamapulih && docker compose -f docker-compose.prod.yml exec -T app php artisan mamapulih:backup
```

Pair this with a step that copies the resulting archive off-server — the dashboard's setup checklist reminds you to set this up, but doesn't do it for you.

## Restoring

**This overwrites the current database and private storage.** Only run it when you mean to.

```sh
docker compose -f docker-compose.prod.yml cp ./mamapulih-backup-<timestamp>.tar.gz app:/tmp/
docker compose -f docker-compose.prod.yml exec app php artisan mamapulih:restore /tmp/mamapulih-backup-<timestamp>.tar.gz
```

Drop `--force` is not needed — the command asks for confirmation before it touches anything. Add `--force` only for a scripted/non-interactive restore where you've already confirmed out of band.

## What's not included

Nothing outside the container needs backing up separately — there's no local file storage or state outside the database and `storage/app`. Your `.env` file (app key, database password) isn't part of the backup; keep a copy of it somewhere safe too, since losing `APP_KEY` makes encrypted data (SMTP passwords, payment credentials, sensitive-case notes) unrecoverable even with a database backup.
