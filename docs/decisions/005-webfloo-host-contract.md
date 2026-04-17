# ADR 005 — Webfloo Host Contract

**Status:** ACCEPTED (2026-04-17)
**Date:** 2026-04-17
**Decider:** Mike / Bitfloo
**Context branch:** `feat/webfloo-extraction`
**Supersedes:** none
**Related:** ADR 003 (core package as SSOT), ADR 004 (full rename)

---

## Kontekst

Pakiet `bitfloo/webfloo` (standalone Composer package, docelowo `github.com/Bitfloo/webfloo`) wymaga od hosta określonej struktury i wersji framework'u. Bez explicit kontraktu:

- Nowy developer/agent AI nie wie jak zainstalować i wire'ować pakiet.
- Runtime failures trudne do debuggowania (np. `webfloo_user_model()` RuntimeException bez kontekstu).
- Różne hosty mogą mieć różne User models / Filament wersje / DB drivers — pakiet musi jasno ogłosić co gwarantujemy a co nie.

## Decyzja

**Host contract** — explicit lista wymagań. Każdy host instalujący `bitfloo/webfloo` MUSI spełnić:

### 1. Framework wersje (hard requirements via composer.json)

| Component | Version | Why hard |
|---|---|---|
| PHP | `^8.2` | Pakiet używa readonly properties, enums, first-class callable, named args |
| Laravel | `^11.0 \|\| ^12.0` | `illuminate/support` via package require; pakiet testowany tylko na L12 |
| Filament | `^5.0` | Schema API, Heroicon enum, BackedEnum nav icon — v4/v6 incompatible |
| `spatie/laravel-translatable` | `^6.0` | JSON translatable columns |
| `spatie/laravel-permission` | `^7.0` | Shield authorization (Role::, hasRole) |
| `mews/purifier` | `^3.4` | `clean()` helper dla Post content, Blog email |
| MySQL | `8.0+` | `SHOW INDEX` / `SHOW COLUMNS` guards w 4 migracjach, JSON column type |

### 2. User model

Host **MUSI** zdefiniować User model extending `Illuminate\Foundation\Auth\User` (lub subklasę — np. `App\Models\User` default Laravel).

Host **MUSI** ustawić w `config/webfloo.php` (published):

```php
'user_model' => \App\Models\User::class,
```

Bez tego — `webfloo_user_model()` helper rzuca `RuntimeException` z hintem do `php artisan vendor:publish --tag=webfloo-config`.

### 3. Authorization stack (Shield)

Host **MUSI** zainstalować i skonfigurować `bezhansalleh/filament-shield` (transitive przez `spatie/laravel-permission`). Pakiet `webfloo`:
- Nie deklaruje Shield w require — host installs manually.
- Dostarcza `ShieldRolesSeeder` (super_admin + editor, bez newsletter PII — GDPR).
- Wymaga `php artisan shield:generate --all --panel=admin` **PRZED** `db:seed --class=Webfloo\Database\Seeders\ShieldRolesSeeder`.

Sekwencja:

```bash
composer require bitfloo/webfloo
php artisan vendor:publish --tag=webfloo-config
# edytuj config/webfloo.php: ustaw user_model + feature flags
composer require bezhansalleh/filament-shield
php artisan shield:install admin
php artisan migrate
php artisan shield:generate --all --panel=admin
php artisan db:seed --class=Webfloo\\Database\\Seeders\\ShieldRolesSeeder
```

Seeder jest idempotent i **filtruje przez istniejące Permission recordy** — nie crashuje na missing permissions, tylko emituje warning.

### 4. Frontend contract

Pakiet **NIE dostarcza:**
- Publicznego frontu (Vue/Inertia/Blade landing, blog show page) — host dostarcza własny layer.
- `resources/js/` — theme-specific.
- Test scaffold (`tests/` dir) — host owns.
- Layout templates (`app.blade.php`, `landing.blade.php`) — host owns.

Pakiet **dostarcza:**
- Filament admin panel (Resources, PageSettings, Widgets).
- Blade atomic components pod `webfloo::` namespace (Atoms, Molecules, Organisms, Sections) — reusable w admin notifications, mail templates, w frontend jeśli host używa Blade.
- Models z relacjami, scopes, traits.
- Mail template `webfloo::mail.lead-email`.
- API webhook route (`/api/leads/webhook` + `/api/leads/webhook/{externalId}`) gate'owany `webfloo.features.crm`.

### 5. Publishable assets

Host publikuje co chce:

```bash
php artisan vendor:publish --tag=webfloo-config   # config/webfloo.php
php artisan vendor:publish --tag=webfloo-views    # resources/views/vendor/webfloo/
php artisan vendor:publish --tag=webfloo-lang     # lang/vendor/webfloo/{pl,en}.json
```

Published overrides automatycznie wygrywają przez Laravel's asset resolution.

### 6. Database storage

Host **MUSI** provide storage disk `public` (Laravel default). Pakiet używa:
- `storage/app/public/posts/*` — Post featured images, RichEditor attachments
- `storage/app/public/pages/*` — Page meta images
- `storage/app/public/projects/*` — Project images
- `storage/app/public/testimonials/*` — avatars
- `storage/app/public/theme/*` — logo, favicon

Symbolic link via `php artisan storage:link`.

### 7. Feature flags (host decides)

Wszystkie `webfloo.features.*` są opt-in/opt-out flagami. Default z pakietu:

```php
'features' => [
    'blog' => true,
    'portfolio' => true,
    'services' => true,
    'testimonials' => true,
    'faq' => true,
    'newsletter' => true,
    'crm' => true,
    'custom_js' => false,  // security: stored-XSS surface, default off
],
```

Host override w `config/webfloo.php` — patrz ADR 007.

### 8. Locale + default fallback

Pakiet zakłada PL jako source locale. Host `config/app.php` może mieć `locale` = cokolwiek, ale `fallback_locale` = `pl` zalecane (inaczej keys bez tłumaczenia EN wyrenderują się jako klucze w locales innych niż PL).

## Uzasadnienie

1. **Explicit contract eliminates silent failures** — fresh install bez `user_model` rzuca readable exception zamiast null access down the line.
2. **Shield sequence wymaga orderingu** — bez documentation team traci czas na "seeder warn missing permissions" debugging.
3. **Frontend split (backend-first)** — pakiet nie narzuca stack frontu. Host wybiera Vue/Inertia/Blade/React — o ile backend API + admin panel działa.
4. **Feature flags = security surface** — custom_js default false, blog/crm/etc. default on bo większość hostów ich używa.
5. **MySQL-only** — 4 migracje użyją MySQL-specific features (`SHOW INDEX`, JSON column). Porting na Postgres = follow-up milestone.

## Konsekwencje

### Positive
- README w `bitfloo/webfloo` repo ma konkretną checklist install.
- Runtime failures mają actionable error messages.
- Agent AI fresh-cloning pakiet wie co zainstalować i w jakiej kolejności.
- Compliance (GDPR — newsletter PII admin-only) jest baked in.

### Negative
- Pakiet **nie działa out-of-box** bez 5-krokowego install (vendor publish → config edit → shield install → migrate → seed).
- Locked do MySQL — Postgres users muszą czekać na ADR 008+.
- Frontend layer host responsibility — zero-config dla "prosta strona firmowa" nie jest goalem.

### Neutral
- Host może zdecydować nie używać Shield (np. własna auth middleware) — wtedy canAccess() zwraca false dla każdego Filament surface, i host dodaje własne gates.

## Alternatives considered

- **Auto-register Shield via package** — odrzucone, bo Shield to opinionated admin UI, nie każdy host chce. Host keeps control.
- **Bundle User model w pakiecie** — odrzucone (ADR 003), bo User = app concern. Pakiet referuje przez config.
- **Postgres + MySQL dual** — odrzucone dla alpha, abstrakcja DB driver = scope creep.
- **Zero-config install** — odrzucone, bo user_model + shield:generate wymagają explicit setup.

## Implementacja

Ten ADR jest docsimą. Właściwa weryfikacja:

1. README.md w seed commit zawiera sekcję "Requirements" ściągającą z sekcji 1-3.
2. README.md zawiera "Installation" z literal commands z sekcji 3.
3. `webfloo_user_model()` helper throws z hintem (już done, commit `3e16d51`).
4. composer.json package require = sekcja 1 table.
5. Post-install smoke test w CI: migrate + seed + `php artisan route:list` → zielone.
