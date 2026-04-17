# ADR 004 — Full Rename `bitfloo/core` → `webfloo`

**Status:** ACCEPTED (2026-04-16 22:58 — user decyzja: FULL rename + rename PRZED Phase 1.5, bez BC aliasów)
**Date:** 2026-04-16
**Supersedes parts of:** ADR 003 (core package as SSOT — identity tokens)
**Decider:** Mike / Bitfloo
**Context branch:** `feat/webfloo-extraction`

---

## Kontekst

Projekt `bitfloo-web` składa się z dwóch warstw:

1. **Core package** — `packages/bitfloo/core/` — CMS foundation (modele, Filament resources, page settings, helpers). Docelowo ekstraktowane do osobnego repo (`bitfloo/webfloo` na GitHubie). Plan: `docs/plans/webfloo-extraction/.loop-state.md`, aktualnie Phase 1.5 (CRITICAL fixes in-place).
2. **Skórka (theme)** — reszta projektu (`app/`, `resources/`, `config/`, `routes/`) — publiczna strona Bitfloo + implementacja klienta.

**Produkt "webfloo" = core package.** "Bitfloo" = marka firmy (właściciel webfloo) i konkretna skórka tego projektu.

Obecny stan (2026-04-16): semantycznie rename już się rozpoczął (repo docelowe `github.com/bitfloo/webfloo`, katalog planu `webfloo-extraction`), ale technicznie zero zmian — namespace `Bitfloo\Core\`, composer `bitfloo/core`, config key `bitfloo.*`, katalog `packages/bitfloo/core/`.

## Decyzja

**FULL RENAME** — zmiana obejmuje wszystkie warstwy identyfikatorów pakietu core.

| Warstwa | Było | Ma być |
|---------|------|--------|
| Composer package name | `bitfloo/core` | `bitfloo/webfloo` (vendor = org `bitfloo`, package = `webfloo`) |
| PSR-4 namespace | `Bitfloo\Core\` | `Webfloo\` |
| Katalog w monorepo | `packages/bitfloo/core/` | `packages/webfloo/` |
| Plik konfiguracji (w pakiecie) | `config/bitfloo.php` | `config/webfloo.php` |
| Publish tag Laravel | `bitfloo-config`, `bitfloo-migrations`, `bitfloo-views` | `webfloo-config`, `webfloo-migrations`, `webfloo-views` |
| Config key / runtime | `config('bitfloo.user_model')` | `config('webfloo.user_model')` |
| View namespace | `bitfloo::pages.*` | `webfloo::pages.*` |
| Translation namespace | `trans('bitfloo::*')` / `__('bitfloo::*')` | `trans('webfloo::*')` / `__('webfloo::*')` |
| Route middleware alias (jeśli jest) | `bitfloo.*` | `webfloo.*` |
| Service provider FQN | `Bitfloo\Core\...ServiceProvider` | `Webfloo\...ServiceProvider` |
| Package discovery `extra.laravel.providers` | `Bitfloo\Core\...` | `Webfloo\...` |
| CLAUDE.md rule file ref | `packages/bitfloo/core/CLAUDE.md` | `packages/webfloo/CLAUDE.md` |

**NIE zmienia się:**
- Nazwa firmy / marki `Bitfloo` (właściciel webfloo).
- Skórka `resources/themes/bitfloo/`, `VITE_THEME=bitfloo`, `themes/bitfloo/` — to nazwa konkretnej skórki tego projektu (strona firmowa Bitfloo). Druga skórka `default` też zostaje.
- Filament admin panel branding (to warstwa skórki).
- Repo `bitfloo-web` — to projekt skórki Bitfloo, nie core.
- Extraction plan i target repo `github.com/bitfloo/webfloo` — już zgodne z nową nazwą.

## Uzasadnienie

1. **Produkt ma jedną nazwę na każdej warstwie** — composer, namespace, config, docs — inaczej nowi użytkownicy (agenty AI, zewnętrzni developerzy) mają dysonans poznawczy.
2. **Plan ekstrakcji i tak wymaga rename'u** — gdy pakiet trafi do `github.com/bitfloo/webfloo`, nazwa `bitfloo/core` w composer byłaby wprowadzająca w błąd (`require bitfloo/core` z repo `webfloo`).
3. **Separacja marki od produktu** — `Bitfloo` ≠ `Webfloo`. Bitfloo to firma wydająca Webfloo. Ten sam wzorzec co Laravel (firma Laravel LLC) ≠ nazwa produktów (Forge, Vapor, Nova).
4. **CBC / AI-first** — agenci AI pracują z tokenami. Token `webfloo` jest unikalny i jednoznaczny; `bitfloo/core` jest podatny na konflikty z nazwą firmy.

## Scope zmian — liczby (skan 2026-04-16)

| Obszar | Liczba plików |
|--------|---------------|
| Pliki w pakiecie `packages/bitfloo/core/` zawierające string `bitfloo` | **133** |
| Self-refs w pakiecie — `Bitfloo\Core\` lub `bitfloo/core` | **120** |
| Konsumenci w monorepo (app, config, routes, resources, database, tests, bootstrap) | **46** |
| Wywołania runtime (`config('bitfloo')`, `view('bitfloo::...`, `trans('bitfloo::...`, `@bitfloo`) | **64** (pokrycie częściowo nachodzi na 46) |
| Wzmianki w 14 agentach `.claude/agents/` | **8/14** agentów z `packages/bitfloo/core` ref |
| **Szacunek unikalnych plików do dotknięcia** | **~180–220** |

## Alternatywy (odrzucone)

### A. Cosmetic rename (odrzucony)
Zmienić tylko nazwę produktu w docs/README, zostawić `Bitfloo\Core\` namespace i `bitfloo/core` composer. **Odrzucony** bo produkuje trwałą niespójność nazewniczą i wymaga tłumaczenia "Webfloo to w kodzie Bitfloo\Core" przy każdym onboardingu.

### B. Częściowy rename (odrzucony)
Rename tylko composer + docs, zostawić namespace. **Odrzucony** — namespace i package name powinny być zgodne (zasada PSR-4 / composer 2 idiomatic).

### C. Status quo (odrzucony)
Pakiet zostaje `bitfloo/core`, produkt w marketingu nazywany "Webfloo". **Odrzucony** — user explicit decyzja 2026-04-16 22:53: "teraz core to webfloo".

## Konsekwencje

### Pozytywne
- Jedno-nazwowy produkt w każdej warstwie; zero dysonansu.
- Ekstrakcja do osobnego repo (`github.com/bitfloo/webfloo`) bez breaking composer rename w fazie 5 planu.
- Agenci AI dostają spójny token `webfloo` w całym kontekście.
- ADR 003 zachowuje ważność — SSOT core (teraz pod nową nazwą) dalej obowiązuje.

### Negatywne / Ryzyka
- **~180–200 plików do edycji** — jedna duża kampania (realistycznie 1–2 dni pracy z weryfikacją).
- **Breaking change dla CI / lokalnych env** — jeśli ktoś ma własne `config/bitfloo.php` override w env var lub deployment script, wymagany sync.
- **`bootstrap/cache/packages.php`** zawiera `Bitfloo\Core` — wymaga `composer dump-autoload` + `php artisan package:discover` po rename.
- **Migracje bazy danych** — tabela(e) publikowane przez `bitfloo-migrations` tag nie zmieniają nazw (migracje już się odpaliły). Ale nowe migracje używają nowego tagu.
- **Filament panel resources** — jeśli używają `config('bitfloo.*')` do user_model resolution (widać w `packages/bitfloo/core/config/bitfloo.php`), wymagają re-discovery po rename.
- **Conflict z `feat/webfloo-extraction` Phase 1.5** — obecnie backlog 18 sub-stepów CRITICAL fixes. Rename przecina ten backlog (każdy fix dotyka plików core). **Decyzja:** czy rename PRZED Phase 1.5 (rebranduj najpierw, potem fix CRITICALe na nowym namespace), CZY po Phase 1.5 (fixy najpierw, potem kampania rename). Patrz sekcja "Sekwencjonowanie".

### Neutralne
- Skórka i marka Bitfloo zostają — zero zmian w publicznej stronie.
- Layout `resources/themes/bitfloo/` niezmieniony.

## Sekwencjonowanie (propozycja)

**Rekomendowana kolejność: RENAME NAJPIERW, potem Phase 1.5.**

Argumenty:
- Phase 1.5 dotyka 5–9 plików core. Jeśli robimy rename po Phase 1.5, te pliki edytujemy dwukrotnie.
- Rename to mechaniczna kampania (znajdź-zamień z weryfikacją PHPStan + testy). Phase 1.5 wymaga judgment calls (`custom_js` decyzja, BlogController removal).
- CRITICAL findings w audytach (security, performance) nie są gorsze przy nazwie `Bitfloo\Core` niż przy `Webfloo` — rename nie wprowadza nowych CRITICAL, tylko przenosi scope.
- Każdy commit post-rename będzie w nowym namespace = mniej konfuzji w historii.

**Alternatywa:** Phase 1.5 najpierw, rename potem — jeśli zespół uzna że bieżące CRITICAL (PII leak, broken access control) są bardziej pilne niż mechaniczny rename.

## Plan migracji (high-level — osobny spec w `docs/plans/`)

Przed startem kampanii utworzyć `docs/plans/2026-04-16-webfloo-rename.md` z checklist:

1. Branch: `feat/webfloo-rename` z `feat/webfloo-extraction` po Phase 1.5 (lub przed, wg decyzji).
2. Automated batch:
   - `mv packages/bitfloo/core packages/webfloo` (plus `mv packages/bitfloo` cleanup po fakcie).
   - Replace `Bitfloo\Core\` → `Webfloo\` w 120 plikach core + 46 konsumentów (PSR-4, use statements, @param annotations).
   - Replace `bitfloo/core` → `bitfloo/webfloo` w composer.json (root + core) i lock file.
   - Replace `config('bitfloo.*')` → `config('webfloo.*')` — regex z capture na klucz po kropce.
   - Replace `bitfloo::` → `webfloo::` (view, trans, route namespace).
   - Rename `config/bitfloo.php` → `config/webfloo.php` (w pakiecie i published).
   - Update `composer.json` `extra.laravel.providers`.
3. Regen:
   - `composer dump-autoload`
   - `php artisan package:discover`
   - `php artisan config:clear && php artisan view:clear`
4. Verify:
   - `make check` (pint + phpstan + phpunit).
   - `npm run build:bitfloo && npm run build:default` (themes).
   - Manual smoke test: `make up-dev`, otwórz landing + admin.
5. Update docs & meta:
   - `.claude/rules/documentation-map.md` — path `packages/bitfloo/core/CLAUDE.md` → `packages/webfloo/CLAUDE.md`.
   - `CLAUDE.md` (root) — wszystkie wzmianki `bitfloo/core` → `webfloo/webfloo`.
   - `packages/webfloo/CLAUDE.md` — nagłówek pakietu.
   - `docs/plans/webfloo-extraction/.loop-state.md` — adjust Phase 2 "copy packages/bitfloo/core/*" → "copy packages/webfloo/*".
   - `README.md` (jeśli wspomina core).
6. 14 agentów — batchem:
   - Wszystkie wzmianki `packages/bitfloo/core` → `packages/webfloo`, `Bitfloo\Core\` → `Webfloo\`.
   - Dopisać słowo "webfloo" jako nazwa produktu (1–2 linijki kontekstu).
   - 9 agentów bez `make check` — dopisać verification pattern (agentic-first compliance).
7. Single atomic commit: `refactor(webfloo): full rename bitfloo/core → webfloo` — 180–200 plików, jeden change, rollback łatwy przez `git revert`.

## Metryki sukcesu

- `grep -r "Bitfloo\\\\Core\\|bitfloo/core\\|config('bitfloo\\|bitfloo::" --include="*.php" --include="*.blade.php" --include="*.json" .` → **zero wyników** (z wyjątkiem: historyczne migracje, changelog, ten ADR).
- `make check` zielony.
- Oba buildy tem (default, bitfloo) zielone.
- Manual smoke test landing + admin bez regresji.

## Rozstrzygnięcia

1. **Composer name:** `bitfloo/webfloo` (vendor = org `bitfloo`, package = `webfloo`).
2. **Sekwencjonowanie:** rename **POMIĘDZY Phase 1.5r (DONE 2026-04-16T23:50) a Phase 1 step 3 (consolidation)**. Historyczne uzasadnienie "przed 1.5" zdezaktualizowane — Phase 1.5 ukończona w równoległej sesji z verdict GO/zero CRITICAL. Rename idzie teraz, przed step 3, żeby consolidation doc napisać już w nowym namespace; Phase 2 webfloo repo seed dostaje clean content.
3. **BC aliasy:** NIE. Clean cut, jeden atomic commit, rollback via `git revert`.

---

## Następny krok

Spec migracyjny: `docs/plans/2026-04-16-webfloo-rename.md` — checklist z measurable ACs. Potem kampania na branchu `feat/webfloo-rename`.
