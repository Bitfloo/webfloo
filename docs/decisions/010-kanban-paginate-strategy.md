# ADR 010 — CrmDashboard Kanban Pagination

**Status:** ACCEPTED (2026-04-17)
**Related:** D1 deferred item, `docs/plans/webfloo-extraction/02-extraction-plan.md`
**Implemented:** webfloo@c866939, bitfloo-web@d3cc35a

## Problem

`CrmDashboard::getKanbanLeads()` wywoływał `Lead::inPipeline()->get()` bez limitu. Scope `inPipeline()` (3 statusy) = praktyczny cap dla alfy, ale przy 100+ leadów w kolumnie = memory spike + fat Livewire payload.

## Decyzja

Strategia **A** — per-column cap 25 + "Pokaż więcej (N)" button.

- `CrmDashboard::COLUMN_PAGE_SIZE = 25`
- `columnLimits[$status]` public property, `loadMore($status)` Livewire action inkrementuje o page size
- `getKanbanCounts()` zwraca total per status (badge w nagłówku); `getKanbanLeads()` zwraca limited subset
- Status whitelist w `loadMore()` chroni przed injection
- In-memory cache (`$kanbanLeadsCache`, `$kanbanCountsCache`) — reset na `loadMore` i `updatedSearchQuery`

## Odrzucone

- **B** (infinite scroll) — drag-and-drop komplikuje się przy offsetach
- **C** (virtualization) — wymaga zewnętrznej JS lib + Livewire DOM reconciliation collision
- **D** (hard cap bez UX) — user confusion "gdzie moja reszta"

## Trade-off

List view (`viewMode='list'`) używa tego samego `getKanbanLeads()` — też paginated. Users potrzebujący pełnej listy: `LeadResource` table (Filament paginated + export).

## Revisit

Rozważ B lub C gdy:
- regularnie > 100 leadów w kolumnie, lub
- "Pokaż więcej" feedback negatywny > 2x/tydz
