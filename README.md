# MamaPulih

Free, open-source confinement meal booking system for Malaysian postpartum (*pantang*) kitchens. Any confinement meal business can download MamaPulih, self-host it on its own server, and run bookings, approvals, payments, kitchen prep and delivery from one app — no running costs to the project, no paid APIs required.

The full business flow and development plan live in a local `docs/` folder that is intentionally not published in this repository.

## Status

Pre-release (Sprint 0). Not yet ready for production use.

## Stack

- **Backend:** Laravel 13 (PHP), PostgreSQL
- **Frontend:** Vue 3 + TypeScript via Inertia.js, Tailwind CSS v4
- **Packaging:** Docker + Docker Compose (via [Laravel Sail](https://laravel.com/docs/sail) in development)
- **Languages:** Bahasa Melayu and English throughout

## Local development

Requires Docker Desktop.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate
npm run dev
```

The app will be available at the URL in `APP_URL` (default `http://localhost:8000`). Mailpit's UI (catches local outgoing mail) is at `http://localhost:8025`.

> **Git Bash on Windows:** the `./vendor/bin/sail` wrapper script doesn't recognise Git Bash (`MINGW64`) as a shell and will refuse to run. Use `docker compose exec laravel.test <command>` instead, e.g. `docker compose exec laravel.test php artisan migrate`. WSL2 and macOS/Linux shells are unaffected.

### Quality checks

```bash
vendor/bin/pint --test        # code style
vendor/bin/phpstan analyse    # static analysis
npm run lint                  # ESLint
npm run format                # Prettier check
npm run typecheck             # vue-tsc
npm run i18n:check            # en/ms translation key parity
./vendor/bin/sail artisan test
```

All of the above run in CI on every pull request (see `.github/workflows/ci.yml`).

## Licence

[AGPL-3.0](LICENSE). Free to use, modify and self-host; anyone who offers a modified version as an online service must share their changes.

## Support

Free community support via GitHub issues and discussions. Paid setup, customisation, hosting help and maintenance are available from the maintainer.
