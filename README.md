# Eliflammeem · إلف لام ميم

[![CI](https://github.com/sharjeelz/eliflammeem-git/actions/workflows/ci.yml/badge.svg)](https://github.com/sharjeelz/eliflammeem-git/actions/workflows/ci.yml)
[![License: AGPL v3](https://img.shields.io/badge/License-AGPL%20v3-blue.svg)](./LICENSE)
[![PHP 8.3](https://img.shields.io/badge/PHP-8.3-777BB4.svg)](https://www.php.net/)

Open-source multi-tenant school helpdesk & communication platform — Arabic-first, built for MENA schools.

> Parents, teachers and students report issues. Admins route, respond, and close them. AI triages and spots trends before they escalate.

---

## What it does

- 🎫 **Issue tracking** — parents/teachers submit issues via a passwordless access-code portal; admins triage, assign, comment and close.
- 🏫 **Multi-tenant by subdomain** — every school is isolated (`schoola.eliflammeem.com`); one install serves many schools.
- 👥 **Role-based access** — admin, branch manager, and staff with per-branch and per-category scoping.
- 📊 **Reports & CSAT** — per-branch breakdowns, category trends, SLA, staff performance, customer-satisfaction surveys.
- 📣 **Broadcasting** — email & SMS to all contacts or filtered segments (Twilio + SMTP).
- 🤖 **AI triage (Pro)** — sentiment, urgency flags, theme extraction, trend detection.
- 💬 **RAG chatbot (Pro)** — per-school knowledge base (PDF/Word → pgvector embeddings → Claude).
- 📱 **WhatsApp Business (Pro)** — schools receive issues and reply directly through WhatsApp.
- 🌐 **Arabic-first / RTL** — full bilingual UI (Arabic + English).

## Screenshots

> _Screenshots coming soon. Hosted demo at `demo.eliflammeem.com` (planned)._

---

## Quick start (Docker)

Requirements: Docker + Docker Compose.

```bash
git clone https://github.com/sharjeelz/eliflammeem.git
cd eliflammeem
cp .env.example .env
docker compose up -d
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan tenants:migrate
```

Open:
- Marketing / central domain: http://central.lvh.me:8000
- Nova superadmin: http://central.lvh.me:8000/nova
- Tenant (after provisioning via Nova): http://schoola.lvh.me:8000

> `lvh.me` automatically resolves all subdomains to `127.0.0.1` — no hosts-file changes needed.

Seed a demo school from Nova → Tenants → Actions → **Generate Demo Data**.

---

## Architecture

- **Backend:** Laravel 11 (PHP 8.2+), Stancl Tenancy v3, Spatie Permission, Laravel Nova
- **Frontend:** Blade + Alpine.js + Tailwind + Vite
- **Database:** PostgreSQL 16 + pgvector (for RAG chatbot)
- **Queue:** Laravel database queue (AI, email, notifications)
- **AI:** Python microservice (sentiment/theme analysis) + Anthropic Claude + OpenAI embeddings
- **Integrations:** Twilio (SMS), WhatsApp Business API, Cloudflare Turnstile, SMTP

See [`PROJECT.md`](./PROJECT.md) for the full feature reference and [`CLAUDE.md`](./CLAUDE.md) for code conventions.

---

## Editions

Eliflammeem is developed as **open-core**: the helpdesk, public portal, roles, reports and CSAT are free forever under AGPL-3. Advanced features require a commercial license.

| Feature | Open Source | Pro |
|---|---|---|
| Issue tracking + public portal | ✅ | ✅ |
| Multi-tenancy by subdomain | ✅ | ✅ |
| Roles (admin / branch manager / staff) | ✅ | ✅ |
| CSV import/export (contacts) | ✅ | ✅ |
| Reports & CSAT | ✅ | ✅ |
| Broadcasting (email + SMS) | ✅ | ✅ |
| AI issue analysis & triage | — | ✅ |
| Document library + RAG chatbot | — | ✅ |
| WhatsApp Business integration | — | ✅ |
| Advanced subscription plans | — | ✅ |
| Nova superadmin (multi-tenant ops) | — | ✅ |
| Priority support & guided install | — | ✅ |

Interested in Pro or a guided install? → **szubair01@gmail.com**

---

## License

Dual-licensed:

1. **AGPL-3.0** — free for self-hosted, non-commercial, and AGPL-compatible use. If you modify and run Eliflammeem as a network service, you must release your modifications under AGPL-3.
2. **Commercial license** — for organizations that can't or don't want to comply with AGPL-3 (e.g., proprietary SaaS). Contact `szubair01@gmail.com`.

See [`LICENSE`](./LICENSE) for details.

---

## Contributing

Issues, feature requests and PRs welcome. See [`CONTRIBUTING.md`](./CONTRIBUTING.md).

## Roadmap

- [ ] **Make Laravel Nova optional** — current `composer.json` requires Nova (paid, ~$99/yr). Nova will move to the Pro edition so the open-source core installs cleanly without a Nova license. See [`docs/nova-refactor.md`](./docs/nova-refactor.md) (coming).
- [ ] Hosted public demo (`demo.eliflammeem.com`)
- [ ] English + Arabic translation pass
- [ ] First-time setup wizard (non-Docker)
- [ ] Helm chart for Kubernetes
- [ ] Mobile apps (parent + staff)

> **Current dev-setup note:** until the Nova refactor ships, running this repo locally requires a Laravel Nova license in `auth.json`. CI runs lint only and skips Nova-dependent tests.

## Authors

Built by [@sharjeelz](https://github.com/sharjeelz). Sponsored contributions and install services available.
