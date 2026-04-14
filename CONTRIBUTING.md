# Contributing to Eliflammeem

Thanks for your interest! This project is open-source under AGPL-3 and welcomes community contributions.

## Before you start

- **Bug reports & feature requests** — open a [GitHub issue](../../issues) using one of the templates.
- **Small fixes** (typos, docs, obvious bugs) — go ahead and open a PR.
- **Larger features** — open an issue first to discuss the approach so your work doesn't clash with in-progress changes.

## Development setup

1. Requirements: Docker + Docker Compose, PHP 8.2+ only if you want to run outside Docker.
2. Clone, copy env, start services:

   ```bash
   git clone https://github.com/sharjeelz/eliflammeem.git
   cd eliflammeem
   cp .env.example .env
   docker compose up -d
   docker compose exec app php artisan key:generate
   docker compose exec app php artisan migrate --seed
   ```

3. Provision a test tenant from the Nova superadmin at `http://central.lvh.me:8000/nova`.

## Code style

- PHP 8.2+ with typed properties, constructor promotion, explicit return types.
- Run `vendor/bin/pint --dirty` before committing.
- Form Requests for validation (not inline `$request->validate()`).
- Queue jobs for async work (AI, email, notifications).
- Tenant-scoped models must use the `BelongsToTenant` trait and include `tenant_id` in `$fillable`.

More conventions are documented in [`CLAUDE.md`](./CLAUDE.md).

## Pull request checklist

- [ ] `composer test` passes
- [ ] `vendor/bin/pint --dirty` is clean
- [ ] New feature / fix has at least one test
- [ ] `PROJECT.md` is updated if behavior changes
- [ ] No tenant data, real API keys, or customer information in commits

## Scope of AGPL-3 contributions

By submitting a PR, you agree your contribution is licensed under AGPL-3 (same as the project). If the maintainer decides to include your contribution in the Pro edition, a separate contributor agreement may be requested.

## Code of conduct

Be kind. Be helpful. Don't harass, discriminate, or post anything a school admin wouldn't want to see. The maintainer may remove content or block users at their discretion.
