# Installing MamaPulih

## Prerequisites

- A server or VPS with [Docker](https://docs.docker.com/engine/install/) and the Docker Compose plugin installed. A small instance (1–2 GB RAM) is enough.
- A domain name pointed at the server, with HTTPS (e.g. via a reverse proxy like Caddy or Traefik, or a certificate from Let's Encrypt). The web installer runs over plain HTTP if you skip this, but production use needs HTTPS.
- An SMTP account for sending email (booking confirmations, receipts, reminders) — configured later from the admin screens, not during install.

## 1. Download the compose file

```sh
curl -O https://raw.githubusercontent.com/uekichinos/mamapulih/main/docker-compose.prod.yml
```

## 2. Create your `.env`

Create a `.env` file next to `docker-compose.prod.yml`:

```sh
APP_NAME=MamaPulih
APP_URL=https://your-domain.example
APP_KEY=
DB_DATABASE=mamapulih
DB_USERNAME=mamapulih
DB_PASSWORD=choose-a-strong-password
APP_PORT=8000
```

Generate `APP_KEY` with:

```sh
docker run --rm ghcr.io/uekichinos/mamapulih:latest php artisan key:generate --show
```

Paste the output (including `base64:`) into `APP_KEY` in your `.env`.

## 3. Start the containers

```sh
docker compose -f docker-compose.prod.yml up -d
```

This pulls the prebuilt image, starts PostgreSQL, runs database migrations automatically, and starts the app.

## 4. Finish setup in the browser

Open `http://your-domain.example` (or `https://` once your reverse proxy/HTTPS is in place). You'll land on the web installer.

1. **Setup code** — a one-time code is printed in the container log. Read it with `docker compose -f docker-compose.prod.yml logs app | grep "setup code"`.
2. **Environment check** — confirms PHP, database, and storage are all ready. Fix anything flagged and click **Re-check**.
3. **Database setup** — runs automatically (already done by the container's entrypoint, this step just confirms it).
4. **Admin account** — your name, email, and password. Two-factor authentication setup is required immediately after, before you can reach the dashboard.
5. **Demo data** — choose **Start empty** for a real install, or **Load demo data** to explore with sample packages, zones, and dishes first (safe to clear later from admin).
6. **Finish** — the installer locks itself permanently. `/install` returns 404 from here on.

## 5. Finish setup in the admin screens

The dashboard shows a setup checklist for everything the installer doesn't cover: business profile, SMTP email, payment methods, zones, packages, and menus.

## Next steps

- [BACKUP.md](BACKUP.md) — set up scheduled backups now, before you have real customer data.
- [UPGRADE.md](UPGRADE.md) — how to move to a new version later.
- `legal/privacy-notice-template.md` and `legal/terms-template.md` — fill these in and publish them before taking real bookings (Malaysia's PDPA 2010 requires it).
