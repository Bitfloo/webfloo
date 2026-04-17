# Ekosystem webfloo — architektura i zasady

Ten dokument definiuje **gdzie co dodajesz** w ekosystemie `webfloo`. Jest source of truth dla zespołu — przy wątpliwości "gdzie to iść?" wróć tutaj.

## Trzy repo, trzy role

```
webfloo      (core, PHP)       — logika, modele, Filament, Blade admin, Traits
thezero      (theme, monorepo) — UI; split na 2 warstwy (ADR-012):
                                 - @bitfloo/thezero-core (npm, auto-propagation)
                                 - @bitfloo/thezero-template (GitHub Template, scaffold)
bitfloo-web  (konsument, app)  — content, routing, bitfloo.com-specific
```

Wszystkie trzy repo są pod `Bitfloo` org (private). Konsument instaluje core + core primitives + forked template:

```bash
# Backend dependency (via type: vcs — ADR-011)
composer require bitfloo/webfloo:^0.1

# Frontend primitives (npm, GitHub Packages)
pnpm install @bitfloo/thezero-core:^0.1

# Frontend template: GitHub Template Repo clone (one-time scaffold, divergent after)
# lub istniejący consumer może mieć forked template in-place (jak bitfloo-web w resources/js/themes/)
```

## Trzy warstwy propagacji zmian (ADR-011 + ADR-012)

Każda zmiana w ekosystemie idzie przez jedną z trzech warstw update'u:

| Warstwa | Gdzie | Update flow | Konflikt ryzyko |
|---------|-------|-------------|-----------------|
| **A — CORE** (auto) | `webfloo/src/*` + `thezero/packages/core/src/*` | `feat:`/`fix:` commit → release-please → tag → konsument `composer update` / `pnpm update` → zmiana u wszystkich klientów | Zero (klient NIE edytuje tej warstwy) |
| **B — TEMPLATE** (semi-auto) | `thezero/packages/template/src/*` | Commit w thezero → klient uruchamia `/thezero-sync` skill (Phase 3) → 3-way diff vs `.thezero-sync.md` state → agent decyduje apply/skip | Wysokie (klient customizuje), kontrolowane przez sync skill |
| **C — KLIENT** (unique) | `bitfloo-web/*`, `acme-web/*`, etc. | Klient edytuje u siebie. Żadnej propagacji. | N/A (klient jest ownerem) |

Ta tabela reguluje **kiedy bug fix w core automatycznie rozwiązuje problem u klientów (A)**, **kiedy wymaga kontrolowanego sync (B)**, **kiedy jest sprawą wyłącznie klienta (C)**.

## Reguła #1 — pytanie zerowe

Przy każdym nowym feature zapytaj:

> **"Czy inny klient mógłby tego użyć?"**

| Odpowiedź | Gdzie |
|---|---|
| Tak | `webfloo` (backend) lub `thezero` (frontend) |
| Nie (bitfloo.com-specific) | `bitfloo-web` |
| Nie wiem | Domyślnie `webfloo/thezero` — łatwiej promotować generyczne w dół niż cofać specyficzne w górę |

## Decision matrix — typowe feature

| Feature | Gdzie | Ścieżka |
|---|---|---|
| Nowy content type (Case Study, Event, itp.) | `webfloo` | `src/Models/`, `database/migrations/`, `src/Filament/Resources/` |
| Nowe pole w modelu | `webfloo` | migration + `$fillable` + Filament form field |
| Nowa Filament settings page | `webfloo` | `src/Filament/Pages/` — extends `AbstractPageSettings` |
| Nowy Filament widget (generic) | `webfloo` | `src/Filament/Widgets/` |
| Nowy Filament widget (bitfloo-only) | `bitfloo-web` | `app/Filament/Widgets/` |
| Blade component (admin-facing) | `webfloo` | `src/Components/` + `resources/views/components/` |
| Vue Atom (AppButton, AppIcon) | `thezero` — **core** | `packages/core/src/Atoms/` |
| shadcn-vue primitive (button, input, carousel) | `thezero` — **core** | `packages/core/src/ui/` |
| Composable (useX) | `thezero` — **core** | `packages/core/src/Composables/` |
| Nowa sekcja landing page | `thezero` — **template** | `packages/template/src/Sections/` |
| Molecule (Card, Header, ...) | `thezero` — **template** | `packages/template/src/Molecules/` |
| Organism (Header, Footer) | `thezero` — **template** | `packages/template/src/Organisms/` |
| Form (Contact, Newsletter) | `thezero` — **template** | `packages/template/src/Forms/` |
| Nowy layout Vue | `thezero` — **template** | `packages/template/src/Layouts/` |
| Nowa strona Vue (Pricing, itp.) | `thezero/packages/template/src/Pages/` jeśli generic; `bitfloo-web/resources/js/themes/bitfloo/Pages/` jeśli bitfloo-only |
| Nowy route | `bitfloo-web` | `routes/web.php` |
| Controller (generic webhook/API) | `webfloo` | `src/Http/` |
| Controller (bitfloo-specific) | `bitfloo-web` | `app/Http/Controllers/` |
| Trait (np. `Publishable`) | `webfloo` | `src/Traits/` |
| Service generic | `webfloo` | `src/Services/` |
| Service bitfloo-only | `bitfloo-web` | `app/Services/` |
| Seed data, teksty | `bitfloo-web` | seeders, `Page` model records, settings |
| Migracja dla modelu webfloo | `webfloo` | `database/migrations/` — uruchamia się w konsumencie po `artisan migrate` |

## Flow end-to-end feature — przykład "Case Studies"

1. **webfloo** (backend)
   - Model `CaseStudy` z `@property` PHPDoc, `$fillable`, `$casts`, scopes (`scopeActive`, `scopeOrdered`, `scopeFeatured`)
   - Migration z `down()`
   - Filament Resource w `src/Filament/Resources/CaseStudyResource.php`
   - Policy (jeśli wymagana) — w `bitfloo-web/app/Policies/` (konsument)
   - Test/feature — wg konwencji projektu

2. **thezero** (frontend; **decyzja: core primitive czy template Section?**)
   - `packages/template/src/Pages/CaseStudy/Index.vue` + `Show.vue` (template — brand-influenced)
   - `packages/template/src/Molecules/CaseStudyCard.vue` (template — ma content klienta)
   - `packages/template/src/Sections/CaseStudiesSection.vue` (template — układ per klient)
   - Brak zmian w core (nowy content type nie wymaga primitive)
   - `feat(template): add CaseStudy views` commit → NIE publikuje się (template) — klient dostaje przez `/thezero-sync` (Phase 3) lub manualnie cherry-pick'uje

3. **bitfloo-web** (konsument)
   - `composer update bitfloo/webfloo` → ładuje Model + Resource + migration (warstwa A)
   - `php artisan migrate` — tworzy tabelę
   - `pnpm update @bitfloo/thezero-core` → ładuje nowe primitives jeśli były w tym release (warstwa A)
   - Dla template changes (warstwa B): `/thezero-sync` skill (Phase 3) albo manualny cherry-pick z `Bitfloo/thezero` do `resources/js/themes/bitfloo/`
   - Rejestracja route w `routes/web.php` (Inertia render do `CaseStudy/Index`)
   - Seed/content jeśli trzeba

## Atomic Design — Blade (webfloo) i Vue (thezero)

Obie strony trzymają się tego samego podziału, ale thezero rozdziela na **core** (primitives) i **template** (compositions):

| Poziom | Definicja | Przykłady | thezero location |
|---|---|---|---|
| **Atom** | Bezstanowy, najmniejsza jednostka | Button, Badge, Icon, Heading, Text | `packages/core/src/Atoms/` |
| **ui/** | shadcn-vue primitives — brand-agnostic | Input, Button, Carousel, Alert | `packages/core/src/ui/` |
| **Molecule** | Kompozycja atomów | Card, ServiceCard, ProjectCard, SectionHeader | `packages/template/src/Molecules/` |
| **Organism** | Większa sekcja funkcjonalna | Header, Footer | `packages/template/src/Organisms/` |
| **Section** | Pełna sekcja strony | Hero, FAQ, Portfolio, Services, CTA | `packages/template/src/Sections/` |
| **Form** | Kontaktowe/newsletter | ContactForm, NewsletterForm | `packages/template/src/Forms/` |

**Zasada**: Atoms+ui = core (bo generic, brand-agnostic, updates propagate auto). Molecules i wyżej = template (bo kompozycje są brand-influenced, klient customizuje).

## SSOT — Single Source of Truth

**Każdy fakt, reguła, definicja istnieje w dokładnie jednym miejscu. Reszta to referencje.**

Przykłady:

- Struktura Atomic Design → opisana **raz** tutaj, referencjonowana w webfloo CLAUDE.md i thezero CLAUDE.md
- Filament v5 imports → źródło w webfloo root `CLAUDE.md`, referencjonowane w bitfloo-web
- Architektura npm package thezero → `~/DEV/thezero/docs/ARCHITECTURE.md`, NIE duplikuj tu

## Gdy nie wiesz

1. Rzuć okiem na tę tabelę
2. Zastanów się: "czy inny klient mógłby tego użyć?"
3. W razie wątpliwości — spytaj na Slacku/w zespole PRZED implementacją
4. Jeśli featura się pojawiła tylko "po drodze" dla bitfloo.com, ale wygląda generycznie → zacznij w `bitfloo-web`, potem promotuj do `webfloo` kiedy drugi klient tego potrzebuje (refactor reversible)

## Anti-patterns (nie rób)

- ❌ Vue component w `webfloo` (webfloo to PHP, koniec)
- ❌ Logika biznesowa w `thezero` (theme jest stateless)
- ❌ `hardcode bitfloo.com` w `webfloo` albo `thezero` (oba muszą działać dla dowolnego klienta)
- ❌ Duplikacja kodu między webfloo i bitfloo-web (jeśli to samo = idzie do webfloo)
- ❌ Bezpośredni import z `vendor/` lub `node_modules/@bitfloo/thezero-core/` w kodzie konsumenta (używaj Composer/npm API, nie ścieżek plików)
- ❌ Commit specyficznych `.env`, `composer.lock` w webfloo/thezero (tylko w konsumencie)
- ❌ Edycja `~/DEV/bitfloo-web/node_modules/@bitfloo/thezero-core/` — zmiany robisz w `~/DEV/thezero/packages/core/`, potem `feat(core):` commit → Release PR → publish
- ❌ **Brand colors w `thezero/packages/core/`** — CI `structure` job blokuje; brand tokens idą do `packages/template/src/colors.css` albo klient-local
- ❌ **Nowy Section w `packages/core/`** — Section = kompozycja brand-influenced = idzie do `packages/template/`. CI guard tego NIE łapie; dev review musi
- ❌ **`npm install` / `npm update` przy monorepo thezero dev** — używaj `pnpm` (workspace linking, strict deps)
- ❌ Commit bez Conventional prefix (`feat:`, `fix:`, `docs:`, ...) na main — psuje release-please auto-bump

## Linki

- `thezero` architektura (publikacja, dev workflow, GitHub Packages): <https://github.com/Bitfloo/thezero/blob/main/docs/ARCHITECTURE.md>
- `thezero` CONTRIBUTING: <https://github.com/Bitfloo/thezero/blob/main/docs/CONTRIBUTING.md>
- `webfloo` root `CLAUDE.md` (konwencje Filament v5, Blade, Atomic Design): `/CLAUDE.md` w tym repo
