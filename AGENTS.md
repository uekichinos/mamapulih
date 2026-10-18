# MamaPulih — agent guide

Open-source confinement meal booking system. Full business rules and the sprint plan live in `docs/` (gitignored, local-only — not part of the published repo):

- `docs/confinement-booking-business-flow.md` — what the product does and why
- `docs/confinement-booking-development-plan.md` — stack, sprint plan, data model, decisions log (see §14 for standing decisions, including P15: no AI co-author/contributor trailers on commits or PRs in this repo)

## Stack

Laravel 13 (PHP 8.5) + Inertia.js + Vue 3/TypeScript + Tailwind CSS v4, PostgreSQL 18, Vite. Local dev via Laravel Sail (`./vendor/bin/sail up`). See `docker-compose.yml`.

## Conventions

- Money is stored in sen as integers, never floats.
- Every customer-facing and admin string exists in both `en` and `ms` (`lang/{en,ms}/*.php` for backend, `resources/js/lang/{en,ms}.json` + `vue-i18n` for frontend). `npm run i18n:check` enforces key parity.
- Business rules (pricing, zones, policies) are admin settings, not hard-coded.
- Before committing: `vendor/bin/pint`, `vendor/bin/phpstan analyse`, `npm run lint`, `npm run typecheck`, `npm run i18n:check`, `php artisan test` (or `./vendor/bin/sail artisan test` if running inside Docker) must all pass — this mirrors `.github/workflows/ci.yml`.
- No AI co-author/contributor attribution in commits or PRs (decision P15).
