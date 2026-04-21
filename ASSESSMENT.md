# Assessment Notes

## Assumptions

1. **Single active company per session** — The spec describes commission notes belonging to a company and its branches/employees. I assumed a user browses one company at a time (selected via query parameter `?company_id=`). The seed includes one company (Spar Group) to keep the demo focused.

2. **Employee is a separate entity** — Rather than attaching commission notes directly to a User, I modelled `Employee` as a separate table linked to a Company/Branch. This reflects real-world scenarios where the person being paid may not have a system account.

3. **Amount stored in cents** — `amount` is stored as an integer in cents (e.g. 10000 = R100.00). This avoids floating-point rounding issues for financial data. The Vue frontend converts to/from a human-readable rands value.

4. **Payment date is required** — A commission note without a payment date is incomplete; the field is required and validated.

5. **Soft deletes not required** — The spec did not mention an audit trail or soft-delete requirement, so `commission_notes` uses hard deletes. Adding `SoftDeletes` would be a one-line change if needed.

---

## Permission Model

Roles and permissions are managed by **Spatie Laravel Permission**.

| Action              | `manager` role | `viewer` role |
|---------------------|:--------------:|:-------------:|
| View commission notes (index) | ✅ | ✅ |
| Create commission note | ✅ | ❌ |
| Update **own** note | ✅ | ✅* |
| Update **any** note | ✅ | ❌ |
| Delete commission note | ✅ | ❌ |

> \* A viewer who created a note (`created_by` matches) may edit it. This models the real-world rule that a salesperson can correct their own submission before a manager reviews it.

The policy lives in `app/Policies/CommissionNotePolicy.php`. Authorization is triggered via `$this->authorize()` in the controller, using the `AuthorizesRequests` trait on the base `Controller` class.

Permissions seeded:
- `manage commission notes` — assigned to `manager` role
- `view commission notes` — assigned to both `manager` and `viewer` roles

---

## Design Decisions

### Service Layer (`CommissionNoteService`)

Business logic (create / update) is extracted into a service class rather than living in the controller. This keeps the controller thin and makes the logic independently testable.

### Inertia + Vue 3

The spec called for Inertia.js with Vue 3. The single `Index.vue` page handles list, create, and edit in one component using Pinia for local UI state (selected note, modal visibility). This avoids unnecessary page transitions for a CRUD-heavy interface.

### Pinia Store

`commissionNoteStore` manages:
- The currently selected note being edited
- Create/edit modal open state
- Optimistic UI helpers

The store is intentionally thin — the source of truth is the server data passed via Inertia props.

### TypeScript

All Vue props, Pinia state, and API payloads are fully typed using interfaces in `resources/js/types/models.ts`. This catches shape mismatches at build time rather than runtime.

---

## Trade-offs

- **No pagination** — With a small dataset the full list is acceptable. For production, cursor-based pagination would be added to `CommissionNoteController::index()`.
- **No real-time updates** — Inertia's `router.reload()` is used after mutations. WebSockets were out of scope.
- **Frontend validation mirrors backend** — The Vue form validates client-side for UX, but the canonical validation is in `StoreCommissionNoteRequest` / `UpdateCommissionNoteRequest`.

---

## Production Hardening (not implemented — out of scope)

- Rate limiting on POST/PUT endpoints
- `APP_KEY` rotation strategy
- Database connection pooling (PgBouncer / ProxySQL)
- Redis for cache and session instead of array/file
- Horizon for queue monitoring
- Telescope / Sentry for error tracking
- HTTPS termination at load balancer; `FORCE_HTTPS=true` in env
- Read replicas for reporting queries
- Audit log table (`commission_note_audits`) with Eloquent observers
