# Webfloo Modules

Logiczne grupowanie domeny pakietu `bitfloo/webfloo`. Każdy moduł to **dokumentacyjny contract** — pakiet zachowuje flat `src/` filesystem (Laravel library convention), ale rejestr `config/webfloo-modules.php` mapuje Resources/Models/Commands/Permissions do modułów dla:

1. **Discoverability** — host devs widzą co należy do którego domain.
2. **Feature flagging** — każdy moduł z `feature_flag` enable/disable przez `config/webfloo.php` `features.*`.
3. **Shield permission grouping** — `view_any_lead` = permission modułu CRM.
4. **Conditional wiring** — `WebflooServiceProvider` nie rejestruje commands disabled modułów; `WebflooPanel` canAccess gates automatycznie schowują Resources gdy flag off.

## Moduły

| Module | Feature flag | Default | Dokumentacja |
|---|---|---|---|
| [pages](pages.md) | — (always-on) | ✅ | Core CMS pages z hierarchią |
| [blog](blog.md) | `webfloo.features.blog` | ✅ | Post + PostCategory z SEO |
| [portfolio](portfolio.md) | `webfloo.features.portfolio` | ✅ | Project z case study fields |
| [services](services.md) | `webfloo.features.services` | ✅ | Service catalog |
| [testimonials](testimonials.md) | `webfloo.features.testimonials` | ✅ | Customer testimonials z rating |
| [faq](faq.md) | `webfloo.features.faq` | ✅ | FAQ z kategoriami |
| [newsletter](newsletter.md) | `webfloo.features.newsletter` | ✅ | Email subscribers (**PII / GDPR**) |
| [crm](crm.md) | `webfloo.features.crm` | ✅ | Lead pipeline + kanban + webhook |
| [menu](menu.md) | `webfloo.features.menu` | ✅ | Navigation menu items |
| [seo](seo.md) | — (always-on) | ✅ | HasSeo trait + sitemap generator |

## Jak wyłączyć moduł

W published `config/webfloo.php`:

```php
'features' => [
    'blog' => true,
    'portfolio' => false,   // ← wyłączone — ProjectResource niewidoczny
    'crm' => false,         // ← wyłączone — Lead pipeline + API webhook wyłączone
    // ...
],
```

Zachowanie po wyłączeniu:
- Resource'y modułu nie pojawiają się w admin panelu (canAccess gate).
- Commands modułu nie są rejestrowane (`php artisan list` ich nie pokaże).
- API routes modułu nie są wire'owane w service provider (np. CRM webhook).
- Migracje modułu **zostają** w `database/migrations/` (bo filesystem flat) — ale tabele nie są usuwane gdy moduł wyłączony. Host decyduje o DROP poprzez osobny cleanup.
- Dane w DB zostają (GDPR-safe — wyłączenie newsletter module NIE kasuje subscribers).

## Host override

```bash
php artisan vendor:publish --tag=webfloo-modules
```

Publikuje `config/webfloo-modules.php` do hosta. Host może:
- Zmienić `resources` / `models` listę (np. dodać własny Resource do modułu).
- Zmienić `permissions` listę (np. rozszerzyć viewer rolę o view_newsletter).

**NIE zalecane:** usuwanie wpisów z rejestru. Filament auto-discovery załaduje Resources niezależnie od rejestru — rejestr jest documentation/grouping layer, nie filter. Jeśli host chce wyłączyć Resource, używa feature flag.

## Adding a new module

1. Dodaj wpis w `config/webfloo-modules.php` (lub host override).
2. Jeśli moduł ma feature flag, dodaj go w `config/webfloo.php` `features.*`.
3. Nowe Resources: umieść w `src/Filament/Resources/` — Filament auto-discovery załaduje.
4. Nowe Commands: dodaj do rejestru `commands` — `WebflooServiceProvider` zarejestruje jeśli moduł enabled.
5. Nowe migrations: `database/migrations/` — Laravel auto-runs w kolejności timestamp.
6. Utwórz `docs/modules/<name>.md` z contractem.
7. Update `docs/modules/README.md` tabelę.
