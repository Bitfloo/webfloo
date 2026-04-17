# Ekosystem webfloo — architektura i zasady

Ten dokument definiuje **gdzie co dodajesz** w ekosystemie `webfloo`. Jest source of truth dla zespołu — przy wątpliwości "gdzie to iść?" wróć tutaj.

## Trzy repo, trzy role

```
webfloo      (core, PHP)       — logika, modele, Filament, Blade admin, Traits
thezero      (theme, Vue)      — UI, komponenty, layouts, sekcje, style
bitfloo-web  (konsument, app)  — content, routing, bitfloo.com-specific
```

Wszystkie trzy repo są pod `Bitfloo` org (private). Konsument instaluje core + theme:

```bash
composer require bitfloo/webfloo
npm install @bitfloo/thezero
```

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
| Vue component (frontend) | `thezero` | `src/Components/{Atoms\|Molecules\|Organisms\|Sections}/` |
| Nowa sekcja landing page | `thezero` | `src/Components/Sections/` |
| Nowy layout Vue | `thezero` | `src/Layouts/` |
| Nowa strona Vue (Pricing, itp.) | `thezero` jeśli generic; `bitfloo-web/resources/js/Pages/` jeśli bitfloo-only |
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

2. **thezero** (frontend)
   - `src/Pages/CaseStudy/Index.vue` + `Show.vue`
   - `src/Components/Molecules/CaseStudyCard.vue`
   - Może `src/Components/Sections/CaseStudiesSection.vue` (dla landing page grid)
   - Update exports w `package.json` jeśli dodajesz nowe sub-path
   - `npm version patch` → push tag → GitHub Actions publikuje

3. **bitfloo-web** (konsument)
   - `composer update bitfloo/webfloo` → ładuje Model + Resource + migration
   - `php artisan migrate` — tworzy tabelę
   - `npm update @bitfloo/thezero` → ładuje nowe Vue components
   - Rejestracja route w `routes/web.php` (Inertia render do `CaseStudy/Index`)
   - Seed/content jeśli trzeba

## Atomic Design — Blade (webfloo) i Vue (thezero)

Obie strony trzymają się tego samego podziału:

| Poziom | Definicja | Przykłady |
|---|---|---|
| **Atom** | Bezstanowy, najmniejsza jednostka | Button, Badge, Icon, Heading, Text |
| **Molecule** | Kompozycja atomów | Card, ServiceCard, ProjectCard, SectionHeader |
| **Organism** | Większa sekcja funkcjonalna | Header, Footer |
| **Section** | Pełna sekcja strony | Hero, FAQ, Portfolio, Services, CTA |
| **Form** | (thezero) Kontaktowe/newsletter | ContactForm, NewsletterForm |
| **ui/** | (thezero) shadcn-vue primitives — kopiowane, nie modyfikowane bez powodu | Input, Button, Carousel, Alert |

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
- ❌ Bezpośredni import z `vendor/` lub `node_modules/@bitfloo/thezero/` w kodzie konsumenta (używaj Composer/npm API, nie ścieżek plików)
- ❌ Commit specyficznych `.env`, `composer.lock` w webfloo/thezero (tylko w konsumencie)
- ❌ Edycja `~/DEV/bitfloo-web/node_modules/@bitfloo/thezero/` — zmiany robisz w `~/DEV/thezero/`, potem republish

## Linki

- `thezero` architektura (publikacja, dev workflow, GitHub Packages): <https://github.com/Bitfloo/thezero/blob/main/docs/ARCHITECTURE.md>
- `thezero` CONTRIBUTING: <https://github.com/Bitfloo/thezero/blob/main/docs/CONTRIBUTING.md>
- `webfloo` root `CLAUDE.md` (konwencje Filament v5, Blade, Atomic Design): `/CLAUDE.md` w tym repo
