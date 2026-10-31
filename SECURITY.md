# Security Policy

## Reporting a vulnerability

Please **do not** open a public GitHub issue for security vulnerabilities.

Instead, use GitHub's private vulnerability reporting: go to the **Security** tab of this repository and click **Report a vulnerability**. This opens a private conversation with the maintainer.

Please include:

- A description of the vulnerability and its impact
- Steps to reproduce
- The version/commit you tested against

## Supported versions

Since every installation is self-hosted, only the latest released minor version receives security fixes. Security releases are published as patch versions with a notice in the release so self-hosted installs know to upgrade.

## Response

The maintainer will acknowledge reports as soon as possible and keep you informed as a fix is developed. Credit is given in the release notes unless you prefer otherwise.

## Security review log

### 2026-10-31 — pre-v1.0 review (Sprint 5)

Walked the decided security measures against actual code:

- **Security headers** — none existed. Added `SetSecurityHeaders` middleware (global, on every response): CSP, `X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`, and HSTS (only sent over HTTPS requests, so an in-progress HTTP-only install can't lock itself out).
- **Auth rate limiting** — confirmed present: staff login (Breeze's built-in `LoginRequest::ensureIsNotRateLimited()`), customer login (`AuthenticatedCustomerSessionController`), 2FA challenge (`TwoFactorChallengeController`), password reset link (`throttle:6,1`) — all 5 attempts before a cooldown.
- **Installer setup code** — confirmed present: hashed, 24-hour expiry, 5 wrong attempts then a 15-minute lock (`InstallationService`).
- **Secrets at rest** — confirmed encrypted: SMTP password (`SmtpSettings`, `Crypt::encryptString`), payment provider credentials (`PaymentProvider::$casts`, `encrypted:array`), staff 2FA secret (`User::$casts`), sensitive-case reason and diet-profile notes (`Booking`, `DietProfile`).
- **Consent links** — confirmed: token stored as a SHA-256 hash, not the raw token (`ConsentService`).
- **CSRF** — Laravel's default protection is active on every stateful route (unchanged).
- **File downloads** (receipts, refund proofs) — these go through authenticated controller actions with an explicit ownership check (`abort_unless($receipt->booking->customer_id === ...)`), not signed temporary URLs as originally sketched in the dev plan. Reviewed and accepted as an equivalent (arguably stronger, since it requires re-authenticating rather than trusting a bearer-style link) — not changed.

No other gaps found in this pass. Not yet done: a full accessibility review (tracked separately), and this log itself should be re-run before each major version.
