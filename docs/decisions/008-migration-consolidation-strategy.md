# ADR 008 — Migration Consolidation Strategy

**Status:** ACCEPTED (2026-04-17)
**Related:** ADR 005, D3 deferred item

## Problem

27 migracji w `database/migrations/` — iteracyjna historia Phase 1.5 hardening. `2026_03_29_000004_fix_migration_inconsistencies.php` patchuje migracje 1–9. Bitfloo-web prod ma applied wszystkie 27, schema konsystentny z runtime.

## Decyzja

**Strategia B** — freeze existing jako legacy, forward-only dla nowych.

- Migracje `2025_01_01_000001_*` – `2026_04_17_000001_*` **nie-dotykane** (allowed: docblock comment "frozen, see ADR 008")
- Nowe migracje tylko `2026_04_18_*` i później — żadnego date-travel do starych plików
- Author profiles split = przyszły ADR 011 z data migration strategy, nie "cleanup"
- Strategia A (squash do initial_schema) = dopiero przy 2.0 major bump

## Odrzucone

- **A. Squash** — łamie bitfloo-web prod (`migrate:status` fails)
- **C. Hybrid** — ambiguous boundary "co jest applied", fragile

## Konsekwencje

- Zero prod risk
- Dirty migration log indefinitely — fresh hosts dostają 27 migracji w serii
- D3 (`docs/plans/migration-cleanup.md`) → RESOLVED jako nie-goal dla 1.x

## Revisit

Strategia A rozważana przy:
- 2.0 major version (breaking release)
- Migration count > 50
- Recurring confusion > 3x/6 mies.
