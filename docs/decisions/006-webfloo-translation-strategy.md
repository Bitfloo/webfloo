# ADR 006 — Webfloo Translation Strategy

**Status:** ACCEPTED (2026-04-17)
**Date:** 2026-04-17
**Decider:** Mike / Bitfloo
**Context branch:** `feat/webfloo-extraction`
**Related:** ADR 003 (core package SSOT), ADR 005 (host contract)

---

## Kontekst

Pierwotny core (`bitfloo/core` przed rename) miał **zero** `__()` / `trans()` calls w `src/`. Wszystkie Filament labels, nav groups, form labels, table columns — hardcoded po polsku. Plus `PluginTranslationRegistry` singleton był zarejestrowany ale `loadTranslationsFrom()` nigdy nie wywołany — i18n infra technicznie broken.

Audit findings:
- core-guardian CRITICAL #2: 85 hardcoded polskich stringów w 20 plikach, zero `__()` calls
- code-reviewer B5: i18n scaffolding nie działa
- performance-auditor: N/A (nie wpływa na perf)

Po Phase 1.5h (infra bootstrap) + 1.5i (wave 1 — Resource labels + 25 keys en.json) — foundation stoi, ale:
- Form labels, table column labels, filter labels nadal hardcoded PL (deferred D2)
- Blade templates `bitfloo::mail.lead-email.blade.php` (teraz `webfloo::`) częściowo PL
- `resources/views/components/**` (atomic) — mix PL/EN komentarze + PL UI strings

Nowy dev (np. host adopting pakiet do non-PL projektu) potrzebuje wiedzieć: co jest tłumaczone, co nie, jak dodać kolejną locale, jak override host-side.

## Decyzja

Pakiet `bitfloo/webfloo` adoptuje **JSON-based translations z PL jako source locale**.

### 1. Locale model

- **Source locale = PL.** Klucze translacji są polskimi stringami samymi w sobie.
- Inne locales (en, de, itd.) mapują PL key → target string w osobnych plikach JSON.
- Laravel's `__('Edytuj')` zwraca `'Edytuj'` gdy `app()->getLocale() === 'pl'` (bo klucz = wartość), a gdy `'en'` — zwraca wartość z `en.json['Edytuj']` (np. `"Edit"`).

### 2. Plik structure

```
packages/webfloo/lang/
├── README.md      ← strategy docs dla developera
├── pl.json        ← INTENTIONALLY EMPTY `{}` (PL self-identifying)
└── en.json        ← 25 keys PL→EN po wave 1, rośnie per wave
```

Pliki loadowane przez `WebflooServiceProvider::boot()`:

```php
$this->loadJsonTranslationsFrom(__DIR__.'/../lang');
```

Publish tag `webfloo-lang` pozwala hostowi override per-key:

```bash
php artisan vendor:publish --tag=webfloo-lang
# → lang/vendor/webfloo/{pl,en}.json
```

Laravel automatycznie merguje: host value wygrywa per key, pakiet fills gaps.

### 3. Inertia/Vue side — osobny mechanizm

`PluginTranslationRegistry` singleton zostaje jako **parallel mechanizm** dla Inertia props (host app serwuje `t()` function JS-side z JSON blobem zwróconym przez props share). Rozdzielne od Laravel Translator:

- Laravel `__()` → obsługuje backend (Filament labels, emails, validation messages).
- `PluginTranslationRegistry::getForLocale()` → obsługuje frontend (Inertia props, Vue components).

Obie ścieżki ładują te same pliki JSON. **Nie konsolidować.** Ingerowanie w jedno łamie drugie.

### 4. Migration waves

Pakiet migruje w falach (priorytet: user-visible admin UI > dev-facing):

| Wave | Scope | Lokalizacja | Status | Commit |
|---|---|---|---|---|
| **1** | Filament Resource labels (`modelLabel`, `pluralModelLabel`, `navigationLabel`, `navigationGroup`) | 11 Resources | DONE | `fd827d2` |
| **2** | Filament form field labels + table column labels + filter labels + action labels | 11 Resources (focus top-3: Post, Page, Lead first) | DEFERRED | 1.5j |
| **3** | Filament PageSettings labels (SiteSettings, HomePageSettings, ContactPageSettings, ThemeSettings) | 4 PageSettings | DEFERRED | post-alpha |
| **4** | Mail templates (`webfloo::mail.lead-email`) + Blade notification UI | ~5 Blade files | DEFERRED | post-alpha |
| **5** | CLI commands output (SendLeadReminders, GenerateSitemap) | 2 Commands | DEFERRED | optional |

Każda wave = osobny commit `i18n(core): migracja <scope> + en.json wave N`.

### 5. Key naming convention

- **Klucz = polski string literal** (np. `__('Kategoria')`, nie `__('webfloo.post.category')`).
- Uzasadnienie: Laravel native JSON idiom, no key-maintenance overhead, source locale self-documenting.
- Trade-off: zmiana źródłowego PL stringu = rename klucza w en.json. Akceptowalne (rzadkie).

### 6. Namespaced strings (opt-out dla conflicts)

Jeśli pakiet potrzebuje klucza który collides z innym pakietem (np. `'Edytuj'` przez Filament framework + Webfloo), **wtedy** użyj namespace:

```php
__('webfloo::post.category_slug_help')
```

Pliki wtedy w `lang/vendor/webfloo/{locale}.json` (by convention), loadowane przez `loadTranslationsFrom($path, 'webfloo')`. **Nie używane w wave 1-3** — jak tylko zajdzie potrzeba, migrujemy hotspot.

### 7. Host override

Host ma trzy ścieżki override:

1. **Per-key** — `vendor:publish --tag=webfloo-lang` → edytuj `lang/vendor/webfloo/{locale}.json`.
2. **Global** — host's own `lang/{locale}.json` automatycznie wygrywa nad package.
3. **Nowe klucze** — host dodaje własne stringi w `lang/{locale}.json`; pakiet ich nie widzi (OK — to host content).

## Uzasadnienie

1. **PL source = projekt-origin matches source-of-truth locale.** Zmiana PL wymaga refactoru, EN jest organizowanym tłumaczeniem. Inverse (EN source) wymagałby retroactive rename 85+ stringów.
2. **JSON > PHP arrays** — Laravel automatycznie merge'uje, nie ma overhead maintenance osobnych kluczy per string.
3. **`PluginTranslationRegistry` zostaje** — Inertia/Vue potrzebują strukturalnego bloba, Laravel Translator jest backend-only API. Rozdzielna mechanika dwóch konsumentów.
4. **Wave'y, nie big-bang** — migracja 85+ stringów w jednym commicie ryzykuje regresje (typo w kluczu, missing Textual-context). Iteracyjnie → manageable.
5. **Polskie klucze UTF-8 friendly** — ą/ć/ę/ł/ń/ó/ś/ź/ż w `.json` string keys działa bez escaping, czytelne w code review.

## Konsekwencje

### Positive
- Host może dodać nową locale (np. `de.json`) bez touchowania kodu pakietu.
- Pakiet jest self-contained — clone + composer install + `php artisan db:seed` = działający admin w PL.
- i18n infra testable — unit test: `app()->setLocale('en'); expect(__('Kategoria'))->toBe('Category');`.
- Wave approach = incremental value delivery.

### Negative
- Non-PL developer musi znać PL klucze żeby pisać kod w pakiecie (alternative: IDE autocomplete z en.json — workable).
- Zmiana PL źródła = rename w en.json — drift risk bez CI check. Mitigacja: CI task scans missing keys (follow-up milestone).
- Wave 2+ deferred — admin UI nadal w większości PL do alpha.

### Neutral
- Pakiet oczekuje Laravel translator fully bootstrapped (Laravel default, ale headless SDK usage = custom bootstrapping).

## Alternatives considered

- **EN source + PL translation** — odrzucone (85+ stringów retroactive rename, projekt-origin PL).
- **YAML translations** — odrzucone (Laravel JSON idiom standardowy, YAML wymaga extra package).
- **Key-based (`webfloo.post.category`)** — odrzucone dla wave 1 (overhead bez wartości), zostawione jako opt-out dla namespace conflicts.
- **Poedit .po files** — odrzucone (out of Laravel ecosystem, wymaga dedicated tooling).
- **Konsolidacja `PluginTranslationRegistry` z Laravel Translator** — odrzucone, inny konsument (Inertia JSON props vs PHP `__()`).

## Implementacja

- [x] lang/pl.json + lang/en.json + lang/README.md (Phase 1.5h commit `d329259`)
- [x] `loadJsonTranslationsFrom()` w service provider (Phase 1.5h)
- [x] Publish tag `webfloo-lang` (Phase 1.5h)
- [x] Wave 1 — Resource labels migrated, 25 keys (Phase 1.5i commit `fd827d2`)
- [ ] Wave 2 — form/table column labels (DEFERRED, alpha→beta)
- [ ] Wave 3 — PageSettings labels (DEFERRED, post-alpha)
- [ ] Wave 4 — Mail templates (DEFERRED)
- [ ] CI task — scan missing PL keys w en.json (FUTURE)
- [ ] README pakietu — sekcja "Adding a new locale" (part of Phase 2 seed docs)
