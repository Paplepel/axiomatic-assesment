# Assessment Notes

**Author:** Adriaan van Niekerk  
**Stack:** Laravel 13 · Inertia.js · Vue 3 + TypeScript · Pest · Docker (PHP-FPM 8.4 / MariaDB 11 / Nginx)

---

## Assumptions

**Company → Branch → Employee cascade in the UI.**  
When creating a commission note, the user first picks a Company. The Branch dropdown is then filtered client-side to only branches belonging to that company, and the Employee dropdown to only employees of the selected branch. `company_id` and `branch_id` are stored on the commission note itself for fast reporting without needing to traverse the Employee relationship on every query.

**Employee is not a system user.**  
The person receiving commission may not have a login. `Employee` is a first-class entity linked to a Company and Branch; `User` is only the person operating the system.

**Amount is a decimal, not cents.**  
`amount` is stored as `DECIMAL(12,2)` (two decimal places, e.g. `10000.00` = R10 000.00). This avoids the mental overhead of a cents conversion while still being exact in SQL arithmetic.

**Soft deletes out of scope.**  
The brief did not mention an audit trail requirement. Hard deletes are used; adding `SoftDeletes` would be a one-line model change plus a migration if required later.

---

## Permission Design

Roles and permissions are managed by **Spatie Laravel Permission**.

| Permission | `viewer` | `manager` |
|---|:-:|:-:|
| `view commission notes` | ✅ | ✅ |
| `manage commission notes` (create) | ❌ | ✅ |
| `manage companies` | ❌ | ✅ |
| `manage branches` | ❌ | ✅ |
| `manage employees` | ❌ | ✅ |

**Edit / delete is author-only across all resources.** Even a `manager` cannot modify another user's record. This is enforced at three layers: Policy (`created_by === $user->id`), Service (belt-and-suspenders exception), and the Vue frontend (buttons hidden for non-authors).

---

## Tradeoffs

- **Inertia over a separate API** — Keeps auth session-based and removes API boilerplate. The tradeoff is that a mobile app or third-party integration would need a separate API layer added later.
- **Spatie cache is in-process** — Permission checks are fast. After any role change the cache must be reset (`php artisan permission:cache-reset`). A Redis-backed cache with TTL would be preferable at scale.
- **No pagination** — Acceptable for a demo dataset. For production, cursor-based pagination would be added to each `index()` controller method.
- **Synchronous request lifecycle** — Emails, exports, and audit writes happen inside the HTTP request. See Production section below.

---

## Production Hardening

| Area | Change |
|---|---|
| **Queues** | Move email dispatch, PDF/export generation, and audit log writes to a Redis-backed queue with Laravel Horizon for monitoring. |
| **Backups** | Schedule `spatie/laravel-backup` nightly: full DB dump + app files to off-site S3-compatible storage; 30-day retention. |
| **Audit log** | Add `owen-it/laravel-auditing` or Eloquent observers to record every create/update/delete with the acting user and before/after diff. |
| **Rate limiting** | Tighten the login throttle (`throttle:5,1`) to mitigate credential stuffing; apply per-route throttles to mutation endpoints. |
| **Secrets / HTTPS** | Terminate TLS at the load balancer. Rotate `APP_KEY` and DB credentials via a secrets manager (Vault / AWS Secrets Manager); never commit `.env`. |
| **Soft deletes** | Enable `SoftDeletes` on all models so records are recoverable and referential integrity is preserved in audit queries. |
| **CI pipeline** | GitHub Actions: `composer install → php artisan test → npm run build` on every PR; merge blocked on failure. |
| **Observability** | Telescope (staging only, auth-gated) + Sentry for production error tracking; structured JSON logging via a log aggregator. |
