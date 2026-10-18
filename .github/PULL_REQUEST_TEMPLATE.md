## Summary

<!-- What does this PR do, and why? Link the accepted issue if there is one. -->

Closes #

## Checklist

- [ ] Tests added or updated (failing-first test for bug fixes)
- [ ] Every customer-facing and admin text in BM and EN (`npm run i18n:check` passes)
- [ ] Roles and permissions checked (Kitchen can't reach Owner-only data)
- [ ] Message safety respected (no automatic messages for compassionate, medical-paused or do-not-contact bookings)
- [ ] Works on a 360px phone screen
- [ ] Database migrations safe for existing installs
- [ ] `vendor/bin/pint --test`, `vendor/bin/phpstan analyse`, `npm run lint`, `npm run typecheck` all pass locally
- [ ] Changelog entry added under `[Unreleased]`
- [ ] Commits signed off (DCO)
