# ADR 008 — Migration Consolidation Strategy

**Status:** ACCEPTED (2026-04-17)
**Date:** 2026-04-17
**Decider:** Mike / Bitfloo
**Context branch:** main
**Related:** ADR 005 (host contract), D3 deferred item (docs/plans/migration-cleanup.md)
**Gate:** unblocks future D3 implementation (nie samo D3)

---

## Kontekst

`bitfloo/webfloo` alfa ma 27 migracji w `database/migrations/`. Historia ewoluowała iteracyjnie przez Phase 1.5 hardening — niektóre migracje patchują wcześniejsze zamiast konsolidować. Konkretne przypadki:

- `2026_03_29_000004_fix_migration_inconsistencies.php` — 230 LOC hot-fix patchujące migracje 1–9
- Translatable column duplication między package i host-level migrations
- `users` table invasion (author_profile fields w kolumnach głównej tabeli)

Push-gate + performance-auditor + tests działają green mimo "brudnej" historii. Istniejąca bitfloo-web prod ma applied wszystkie 27 — schema jest konsystentny z run-time behavior.

**Pytanie strategiczne:** consolidate (2.0 major version), freeze jako-jest (1.x backward compat), czy hybrid?

## Rozważone opcje

| Strategia | Plus | Minus | Risk dla prod |
|---|---|---|---|
| **A. Squash** — 27 migracji → pojedyncza `initial_schema.php`, hosts robią fresh `migrate:fresh` | Czyste migration log, łatwy onboarding nowych hostów, explicit "this is the schema" | Łamie existing bitfloo-web prod (`migrate:status` pokazuje "not run" dla faktycznie-applied), wymaga coordinated release + data migration playbook | CRITICAL — prod breaking bez perfect coordination |
| **B. Freeze + 2.0+ forward-only** — Existing 27 migracji są legacy-frozen. Wszystkie NEW changes w `2.x_*` namespace. Fix-up migrations (jak 000004) zostają jako historical artifact | Zero ryzyka dla prod, prostsza governance (stary kod nie jest dotykany), clear "when did this land" semantics | Dirty migration log indefinitely, 27-migration history na każdym fresh install, fix-up files czytelne dopiero z context'u | ZERO — nic nie ruszamy |
| **C. Hybrid** — Consolidate tylko "non-applied" migrations (wchodzące w 1.5+), zostaw applied jako frozen. Rozróżnienie przez explicit version marker | Compromise — czyścimy co możemy bez prod ryzyka | Ambiguous boundary "co jest applied", fragile assumption że wszystkie środowiska są w sync, dwie reguły w head | MEDIUM — wymaga perfect inventory state per environment |

## Decyzja

**Strategia B** — freeze existing 27 migracji jako legacy, wszystkie nowe zmiany w forward-only mode.

### Konkretne reguły

1. **Frozen set (v1.x):** migracje `2025_01_01_000001_*` — `2026_04_17_000001_*` (27 plików current jako stan 2026-04-17) są **nie-dotykane**. Nie rename, nie merge, nie delete, nie edit bodies. Jedyny akceptowalny edit: komentarz docblock dopisujący "frozen 2026-04-17, see ADR 008".

2. **Fix-up migrations jako historical artifact:** `2026_03_29_000004_fix_migration_inconsistencies.php` pozostaje jako-jest. Plik rozwiązany w czasie, history preserved. Nowi hostowie po `migrate --force` dostaną applied state identyczny z prod.

3. **Forward-only namespace:** wszystkie future migrations w prefiksie `2026_04_18_*` i później. Żadnego "date travel" back do roku 2025 by insert intermediate migration. Jeśli trzeba fix'nąć strukturę starej migracji — nowy migration plik z explicit reference do tej który fix'uje.

4. **Author profiles split** (D3 sub-item): zostaje `STAYS-NOW` per extraction plan. Future split = nowy migration tworzący `author_profiles` tabelę + data migration kopiujące user.bio/social_links/avatar_path, z dual-write window, na końcu DROP COLUMN z users. Ta praca = ADR 011 (not written yet).

5. **Squash trigger:** Strategia B staje się trigger'em dla Strategy A tylko w momencie 2.0 major version (breaking release). Wtedy jest explicit "fresh start" za consent hostów. Decyzja odkładana do momentu gdy webfloo wychodzi z alphy (post beta).

### Uzasadnienie wyboru

- **Zero prod ryzyka:** alfa ma jedną produkcyjną instancję (bitfloo-web). Squash łamie ją. Frozen nie.
- **Migration log jest historyką, nie aspirational API:** 27 migracji to zapis co się stało, nie "idealny schema". Próba przepisania historii = rewrite git + wszystkie side-effects.
- **Fresh hosts absorbują koszt:** nowi konsumenci dostają 27 migracji jako jedną serię. Faktyczna egzekucja = seconds. Koszt = wizualny bloat `migrate:status`. Akceptowalny dla alpha.
- **Forward-only eliminuje completely confusion:** jedna reguła ("migracje po dzisiaj = standard, wcześniejsze = frozen") zamiast matrix ("co było applied gdzie, kiedy").
- **Squash zawsze możliwy później:** frozen state może być squashed w 2.0 bez utraty informacji (git history, ADR docs zachowują kontekst). Odwrotnie: squash now + realize problem = recovery path trudny.

## Konsekwencje

### Pozytywne

- Natychmiastowa klarowność — "nie dotykaj tych plików" to prostsza reguła niż "squash tylko niektóre"
- Development velocity nie blokowany przez migration governance — piszesz nowy migration, nie myślisz o legacy
- Prod bitfloo-web zachowany w stanie working bez intervention
- D3 "migration cleanup" staje się **explicit nie-goal** dla 1.x — usuwa presję "kiedyś trzeba to posprzątać"

### Negatywne

- Dirty migration log permanentnie widoczny w `database/migrations/`
- `fix_migration_inconsistencies.php` (230 LOC) jest kognitywny overhead gdy ktoś czyta migracje — mitigated przez docblock "frozen artifact, see ADR 008"
- Nowi deweloperzy (jeśli kiedyś) muszą przeczytać ADR 008 żeby zrozumieć dlaczego nie optymalizujemy

### D3 post-ADR 008 scope

Po accept ADR 008, D3 item w `docs/plans/migration-cleanup.md` przechodzi z **BLOCKED** w **RESOLVED (nie-implement)**. Plik `migration-cleanup.md` zostaje jako historical decision trail ale nie wymaga kodu.

Future migration-related pracy (author profiles split etc.) wymagają osobnego ADR (ADR 011+) z explicit data migration strategy, **NIE** general "cleanup" framing.

## Revisit triggers

**Otwórz Strategy A squash decision gdy:**
- Webfloo hits 2.0 (breaking release planned) — wtedy fresh initial_schema.php w ramach 2.0 migrations, stare frozen jako `pre-2.0/` archive dir lub git tag reference
- Migration count przekracza 50 (czysta waga pliku)
- Recurring developer confusion (> 3 razy w 6 mies.) o "dlaczego X wygląda tak" — wtedy squash może być worth to UX cost

Decision reopen: nowy ADR 008.1 lub ADR 020 z konkretnym scenario.

## Implementation refs

- **Frozen set:** `webfloo/database/migrations/` na commit `ad93373` (27 plików)
- **Gate dla D3:** `docs/plans/migration-cleanup.md` — aktualizować status po accept tego ADR
- **Related ADRs:** 005 (host contract — migration ownership), 006 (translation strategy — wyjaśnia JSON columns), 007 (feature flags — orthogonal do migration governance)

---

**Status dokumentu:** ACCEPTED. Żadne follow-up actions na kodzie migracji nie są wymagane. D3 placeholder plan może być updated ze statusu BLOCKED → RESOLVED.
