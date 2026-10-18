# Changelog

All notable changes to this project will be documented in this file. The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/).

## [Unreleased]

### Added

- Project scaffold: Laravel 13 + Inertia.js + Vue 3/TypeScript + Tailwind CSS v4, PostgreSQL 18.
- Local development via Laravel Sail (Docker Compose with Postgres + Mailpit).
- BM/EN localisation scaffold: backend `lang/{en,ms}`, frontend `vue-i18n` with a working language switcher.
- Coding-standards tooling: Laravel Pint, Larastan (level 7), ESLint + Prettier, `vue-tsc`.
- GitHub Actions CI: code style, static analysis, security audits, backend tests (Pest + PostgreSQL), frontend tests (Vitest), build.
- Community files: README, LICENSE (AGPL-3.0), CONTRIBUTING, CODE_OF_CONDUCT, SECURITY, issue/PR templates.
