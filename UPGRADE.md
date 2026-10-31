# Upgrading MamaPulih

MamaPulih follows [semantic versioning](https://semver.org/) (`v1.2.3`). Breaking changes only happen in a major version bump, and are called out in that release's notes with migration steps.

## Before you upgrade

1. **Back up first** — see [BACKUP.md](BACKUP.md). Always have a current backup before pulling a new image.
2. Read the [release notes](https://github.com/uekichinos/mamapulih/releases) for the version you're moving to, especially any "Breaking changes" section.

## Steps

Set the version you're upgrading to in your `.env` (or pass it inline):

```sh
docker compose -f docker-compose.prod.yml pull
APP_VERSION=v1.2.3 docker compose -f docker-compose.prod.yml up -d
```

The container's entrypoint runs `php artisan migrate --force` automatically on every start, so database schema changes apply without a separate step.

## Rolling back

If something goes wrong, pull the previous tag the same way (`APP_VERSION=v1.2.2`) and restore your pre-upgrade backup if the new version's migrations changed data in a way the old code doesn't expect.

## Staying on `latest`

`docker-compose.prod.yml` defaults `APP_VERSION` to `latest`, which always points at the most recently released version. For a production install, pin a specific version tag instead so an upgrade is always something you choose, not something that happens on a routine `docker compose pull`.
