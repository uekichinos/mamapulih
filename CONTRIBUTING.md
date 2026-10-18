# Contributing to MamaPulih

Thanks for considering a contribution. MamaPulih is maintained by one developer and built in public — outside contributions are welcome and reviewed.

## Before you start

- For anything larger than a small fix, open a GitHub issue with the `proposal` label first. The maintainer will label it `accepted`, `plug-in`, or `out of scope`, with a reason, before you start writing code.
- Core covers what every confinement kitchen needs (booking, capacity, payment recording, kitchen, delivery). Provider-specific or niche features (a payment gateway, a courier service, a special report) belong in drivers or plug-ins, not the core.

## Development setup

See the "Local development" section of [README.md](README.md).

## Pull requests

- One pull request per feature or fix, from a short-lived branch.
- Every PR ships with tests in the same PR. A bug fix includes a test that fails without the fix.
- All CI checks must be green: code style (Pint), static analysis (Larastan), ESLint/Prettier, `vue-tsc`, backend tests, frontend tests.
- Every customer-facing and admin string must exist in both `en` and `ms`.
- Commits must be signed off (Developer Certificate of Origin): add `-s` to `git commit`, or `Signed-off-by: Your Name <email>` in the commit message.
- Do not add AI co-author or contributor trailers (e.g. `Co-Authored-By: ...`) to commits or PR descriptions in this repository, regardless of what tooling generated the change.

## Definition of done

A feature or fix is done only when:

- [ ] Code reviewed and merged through a PR with every CI check green
- [ ] Tests added or updated (failing-first test for bug fixes)
- [ ] Every customer-facing and admin text in BM and EN
- [ ] Roles and permissions checked (Kitchen can't reach Owner-only data)
- [ ] Message safety respected (no automatic messages for compassionate, medical-paused or do-not-contact bookings)
- [ ] Works on a 360px phone screen; keyboard and screen-reader basics checked
- [ ] Database migrations safe for existing installs
- [ ] Docs updated (changelog entry, and admin/install notes where relevant)

## Reporting bugs and requesting features

Use the issue templates. For security vulnerabilities, see [SECURITY.md](SECURITY.md) — do not open a public issue.

## Code of conduct

By participating, you agree to abide by the [Code of Conduct](CODE_OF_CONDUCT.md).
