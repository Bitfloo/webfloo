# ADR-003: SSOT — core package is the single home for domain models

- Date: 2026-04-16
- Status: ACCEPTED
- Relates to: `packages/bitfloo/core/`, Marcin's multi-theme commits (014b8a6..2248622)

## Context

Bitfloo is dual-purpose: (a) company website, (b) reusable CMS foundation for client projects. The CMS foundation lives in the **`bitfloo/core` Composer package** (`packages/bitfloo/core/`). Any client project that consumes the package inherits:

- Domain models: `Lead`, `Post`, `PostCategory`, `Page`, `Project`, `Service`, `Testimonial`, `Faq`, `MenuItem`, `NewsletterSubscriber`, `Setting`, `LeadActivity`, `LeadReminder`, `LeadTag`
- Behaviour traits: `HasActive`, `HasFeatured`, `HasSeo`, `HasSlug`, `Publishable`, `Sortable`
- Database migrations: `packages/bitfloo/core/database/migrations/` (26 files)

During the multi-theme refactor (branch `main`, commits `d16a36b` → `2248622`), 14 models and 6 traits were **copy-pasted** from `Bitfloo\Core\Models\*` / `Bitfloo\Core\Traits\*` into `App\Models\*` / `App\Models\Traits\*`. Files are byte-identical except for namespace. No migrations were added in `database/migrations/` because the tables already exist via the core package.

Problem: this duplicates SSOT. Every future schema change now has two code paths. Agents editing `app/Models/Lead.php` will drift from `packages/bitfloo/core/src/Models/Lead.php` silently.

Options considered:

1. **Keep duplicates in `app/Models/`** — simple but breaks SSOT. Every bugfix applied twice, or one copy rots. Rejected.
2. **`App\Models\Lead extends \Bitfloo\Core\Models\Lead`** — legitimate pattern for app-level overrides (extra scopes, tenant scoping, etc.). Valid when the app actually needs to override. Rejected as default because no override is currently needed and empty subclasses are noise.
3. **Use `Bitfloo\Core\Models\*` directly** — references the package as SSOT, no shadowing, no drift. Accepted.

## Decision

**Option 3.** The `bitfloo/core` package is the single source of truth for the 14 domain models and 6 traits listed above. `App\Models\` must NOT contain copies.

Allowed in `App\Models\`:
- `User` (Laravel default, app-specific).
- Future app-only models that do NOT belong in the reusable CMS foundation.
- **Thin subclasses** (`extends Bitfloo\Core\Models\Foo`) ONLY when the app actually overrides behaviour. Empty subclasses are forbidden.

Allowed in `App\Models\Traits\`:
- Nothing from the list above (`HasActive`, `HasFeatured`, `HasSeo`, `HasSlug`, `Publishable`, `Sortable`). Import from `Bitfloo\Core\Traits\*`.

## Consequences

- Filament resources, controllers, tests must import from `Bitfloo\Core\Models\*`.
- If a client project needs an app-level override, create a thin subclass — don't duplicate the class body.
- When adding a new CMS-foundation model, it goes to `packages/bitfloo/core/src/Models/` with a migration in `packages/bitfloo/core/database/migrations/`.
- `.claude/rules/php-patterns.md` already codifies the trait rule ("all in `packages/bitfloo/core/src/Traits/` — don't copy-paste"). This ADR extends the same rule to models.

## Rejected anti-patterns

- `App\Models\Lead` as a byte-identical copy of `Bitfloo\Core\Models\Lead` — splits SSOT, no override value.
- Adding migrations in `database/migrations/` for tables already created by the core package — they will conflict.
- Editing only one copy when a schema/behaviour change is needed — the other copy silently rots.
