# ADR 007 — Webfloo Feature Flag Matrix

**Status:** ACCEPTED (2026-04-17)
**Date:** 2026-04-17
**Decider:** Mike / Bitfloo
**Context branch:** `feat/webfloo-extraction`
**Related:** ADR 005 (host contract), Phase 1.5g (custom_js XSS gate)

---

## Kontekst

Pakiet `bitfloo/webfloo` wstrzykuje do hosta: 11 Filament Resources (content CRUD), 4 PageSettings (site config), 1 CrmDashboard (kanban leads), 5 Widgets (charts), 1 public API webhook (leads intake), custom_js injection (admin-authored inline JS). Każdy surface jest potencjalnym:

- **Attack surface** (custom_js = stored-XSS, newsletter subscriber = PII exposure)
- **Dependency burden** (host może nie chcieć CRM, tylko blog + portfolio)
- **Performance cost** (kanban unbounded queries, chart widgets per-render)

Pierwotny core miał **partially implemented** feature flags: tylko `features.crm` i `features.newsletter` były sprawdzane, pozostałe features ships unconditionally. Security-auditor flagował to jako "inkonsystencja feature-flag / access-control coupling".

Po Phase 1.5e+f (canAccess gates) wszystkie Resources mają gate pattern:

```php
if (! config('webfloo.features.<flag>', true)) { return false; }
return auth()->user()?->can('view_any_<slug>') === true;
```

Ale **lista flag była niekompletna** — brak `custom_js` (dodana w 1.5g), brak spójnej dokumentacji który flag co robi.

## Decyzja

**Feature flag matrix** — explicit lista wszystkich flag z: default value, scope (co gate'uje), security implication, host recommendation.

### Matrix

| Flag | Default | Scope (co gate'uje) | Security impact | Host recommendation |
|---|---|---|---|---|
| `webfloo.features.blog` | `true` | PostResource + PostCategoryResource admin access, API endpoints (jeśli host wire'uje) | LOW (content CRUD standard) | Enable jeśli host potrzebuje blog/news, disable jeśli single-page site |
| `webfloo.features.portfolio` | `true` | ProjectResource admin access | LOW | Enable dla agency/portfolio sites |
| `webfloo.features.services` | `true` | ServiceResource admin access | LOW | Enable dla service-oferujących brand sites |
| `webfloo.features.testimonials` | `true` | TestimonialResource admin access | LOW | Enable dla sites pokazujących social proof |
| `webfloo.features.faq` | `true` | FaqResource admin access | LOW | Enable dla sites z FAQ/help section |
| `webfloo.features.newsletter` | `true` | NewsletterSubscriberResource admin access + opt-in form endpoints (host wire'uje) | **MEDIUM (GDPR — subscriber PII)** | Enable jeśli host zbiera emaile. **Dodatkowo**: ShieldRolesSeeder wymusza admin-only access do NewsletterSubscriberResource (editor NIE widzi PII). |
| `webfloo.features.crm` | `true` | LeadResource, LeadTagResource, CrmDashboard, lead API webhook (`/api/leads/webhook`), LeadExporter, 5 Widgets | **MEDIUM (PII — emails, names, phone, business data)** | Enable jeśli host potrzebuje lead management. Disable = kanban/charts/webhook routes znikają. |
| `webfloo.features.custom_js` | **`false`** | ThemeSettings Textarea `custom_js` visibility + `ThemeService::getCustomJs()` rendering | **HIGH (stored-XSS as a feature)** | **Keep false dla OSS/multi-tenant.** Flip `true` tylko jeśli: (a) panel access ograniczony do trusted super_admin, (b) operacyjnie potrzebujesz inline JS, (c) rozumiesz brak CSP w pakiecie. |

### Coupling z Shield permissions

Feature flag sprawdzany **PRZED** permission check:

```php
public static function canAccess(): bool
{
    if (! config('webfloo.features.<flag>', true)) {
        return false;   // flag off = surface nie istnieje dla admin UI
    }
    return auth()->user()?->can('view_any_<slug>') === true;
}
```

Znaczenie:
- Flag `false` → ani super_admin, ani editor nie widzi surface (feature = wyłączony product-wide).
- Flag `true` + permission `false` → surface widzi tylko user z permission (np. editor nie ma `view_any_newsletter_subscriber`).
- Flag `true` + permission `true` → pełny dostęp.

**Flag nie zastępuje permission check** — oba muszą być spełnione.

### Routes + jobs + commands też flag-gated

Flag `webfloo.features.crm` w service provider `registerRoutes()` blokuje wire'owanie API routes:

```php
if (config('webfloo.features.crm', true)) {
    Route::prefix('api')->middleware('api')->group(__DIR__.'/../routes/api.php');
}
```

Podobnie — `SendLeadReminders` console command rejestrowany unconditionally, ale jego run() sprawdza flagę przed execute (albo host wyłącza cron task).

### Defense in depth dla `custom_js`

Szczególny case — `custom_js` ma **3 warstwy** obrony (Phase 1.5g):

1. **Config flag** (`webfloo.features.custom_js: false` default) — main gate.
2. **`ThemeService::getCustomJs()` short-circuit** — sprawdza flag PRZED DB read. Nawet jeśli admin zapisał `custom_js` gdy flag był włączony, po wyłączeniu flagi — empty string returned niezależnie od DB content.
3. **Filament Textarea `->visible()`** — pole edycji niewidoczne w admin UI gdy flag off.

Host może teoretycznie bypassować warstwę 3 (ręczny SQL), ale warstwa 2 nadal chroni public output.

## Uzasadnienie

1. **Explicit matrix eliminuje ambiguity** — ops team wie co zostanie wyłączone przy flip flag.
2. **Security-by-default** — `custom_js: false` = OSS-safe default, active opt-in.
3. **Granular scope** — host może wyłączyć cały CRM (blog-only site) albo same features (newsletter yes, blog no).
4. **Flag PRZED permission** — czyszczy mental model: "czy feature istnieje w tym hostingu?" vs "czy ten user ma dostęp?".
5. **Defense in depth dla HIGH-risk features** — custom_js wymaga 3-layer obrony bo single point of failure = stored XSS dla wszystkich visitors.

## Konsekwencje

### Positive
- Host-level opt-in dla content types (nie płacą za features których nie używają).
- Security audit ma konkretny target: sprawdź czy każdy risky feature ma default secure.
- Documentation jest kompletna — każdy flag w ADR-ze ma scope i security impact.
- Custom_js risk zrozumiały (HIGH, opt-in z ostrzeżeniem).

### Negative
- Host który chce WSZYSTKO musi zdecydować świadomie (nie "zapomnieć" włączyć feature) — wymagany config edit po `vendor:publish`.
- Flag matrix musi być sync'owany z kodem — drift risk bez CI scan (follow-up).
- Multi-tenancy edge: jeśli dwóch tenantów hostowanych w jednym app miało różne features → flag scope = global, nie per-tenant. Workaround: osobne service providers per tenant (advanced).

### Neutral
- Wyłączenie flag nie usuwa danych z DB — migration zostają, tylko admin UI + routes wyłączone. Re-enable = natychmiastowy dostęp do istniejących danych.

## Alternatives considered

- **Granular per-permission flags** (`view_newsletter_subscriber_email` vs `view_newsletter_subscriber_name`) — odrzucone. Overkill dla v1, PII coarse-grained (editor widzi wszystko albo nic).
- **Runtime toggle UI** (admin changes flag live) — odrzucone. Config flag z deploy = sensible scope. Follow-up: `Setting`-based flags dla per-env toggle.
- **Env-driven flags** (`WEBFLOO_FEATURE_CRM=true`) — acceptable jako host convention, ale pakiet używa `config/webfloo.php` jako SSOT (host może read env i map do config).
- **Feature plugins** (osobne pakiety: `webfloo-crm`, `webfloo-blog`) — odrzucone dla v1 (scope creep). Kiedy pakiet rośnie > 20 Resources, split na moduły (planowane Phase 4).
- **Flag = true by default dla wszystkiego** — odrzucone dla `custom_js` (security-first).

## Implementacja

- [x] Wszystkie 7 flags (except custom_js) zadeklarowane w `packages/webfloo/config/webfloo.php` `features` array.
- [x] `canAccess()` gate pattern w 11 Resources + 3 PageSettings (Phase 1.5e+f).
- [x] `custom_js: false` default + ThemeService short-circuit + Textarea gate (Phase 1.5g).
- [x] ShieldRolesSeeder enforces newsletter admin-only regardless of feature flag.
- [x] API routes gated by `webfloo.features.crm` w service provider.
- [ ] README pakietu — sekcja "Feature flags" (part of Phase 2 seed docs).
- [ ] CI scan — weryfikacja że każdy Resource ma flag check (follow-up).
- [ ] Runtime audit command: `php artisan webfloo:features` — lista enabled/disabled z config (FUTURE utility).

## Historia

- **Phase 1.5e+f** (`e211a75`) — coupling flag + permission w canAccess pattern.
- **Phase 1.5g** (`edf90d0`) — `custom_js` flag dodany + defense in depth.
- **Rename** (`2a63fbe`) — `bitfloo.features.*` → `webfloo.features.*`.
- **This ADR** — konsolidacja + documentation.
