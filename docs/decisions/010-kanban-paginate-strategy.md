# ADR 010 — CrmDashboard Kanban Pagination Strategy

**Status:** ACCEPTED (2026-04-17)
**Date:** 2026-04-17
**Decider:** Mike / Bitfloo
**Context branch:** `feat/webfloo-extraction` → main
**Related:** D1 deferred item (docs/plans/webfloo-extraction/02-extraction-plan.md), Phase 1.5o
**Implemented:** webfloo@c866939, bitfloo-web@d3cc35a

---

## Kontekst

`Webfloo\Filament\Pages\CrmDashboard::getKanbanLeads()` pierwotnie wywoływał `Lead::inPipeline()->get()` bez `limit()` ani `paginate()`. Scope `inPipeline()` ograniczał zwracane rekordy do 3 statusów z pipeline'u (`new`, `contacted`, `qualified`), co dawało praktyczny cap — lead przechodząc do `converted`/`lost` znika z dashboardu.

Przy alpha-seed'zie pipeline size ~dziesiątki leadów — performance OK. Scale-out risk:
- 100+ leadów w kolumnie = memory spike podczas Livewire state serialization
- Eager-loaded relations (`assignee`, `pendingReminders`, `tags`) amplifikują payload
- Slow render (Blade foreach + Alpine.js drag handlers per card)
- Network transfer (Livewire snapshot w POST body na każdej interakcji)

Push-gate D1 flagował jako MEDIUM ryzyka, DEFERRED na UX decision.

## Rozważone opcje

| Strategia | Plus | Minus | Scope |
|---|---|---|---|
| **A. Per-column pagination** (cap + "Pokaż więcej" button) | Prosty klasyczny pattern, low implementation cost, działa out-of-the-box z Livewire re-render | User nie widzi full overview pipeline od razu | ~100 LOC, 1 commit |
| **B. Infinite scroll** (IntersectionObserver + Livewire lazy) | Gładkie UX, feel kanbanu zachowany | Drag-and-drop między kolumnami komplikuje się przy różnych offsetach, edge cases z DOM sync | ~250 LOC, 2-3 commity |
| **C. Virtualization** (vue-virtual-scroller lub podobne) | Unlimited scale, natywny UX, zero wrapper UX trade-off | Wymaga JS library + custom integracji z Livewire (który ma swoje DOM reconciliation), trudny debugging | ~500 LOC + zewnętrzna dep |
| **D. Hard cap bez UX** (top 50, reszta "niewidzialna") | Zero new code | "Gdzie moja reszta leadów?" — user confusion, broken trust | ~5 LOC |

## Decyzja

**Strategia A** — per-column pagination z `loadMore()` action.

### Parametry

- **Page size:** 25 leadów / kolumna (const `CrmDashboard::COLUMN_PAGE_SIZE`)
- **Initial load:** każda kolumna ma limit 25 (per-status subquery z `->limit(25)`)
- **Badge w nagłówku kolumny:** total count (`getKanbanCounts()`) — user widzi ile leadów jest w pipeline nawet gdy tylko część jest rendered
- **"Pokaż więcej (N)" button** pod leadami — pokazuje hidden count, increment +25 na click via Livewire `loadMore($status)` action
- **Status whitelist:** `loadMore()` waliduje że `$status` jest w `Lead::PIPELINE_STATUSES` (guard against input injection)
- **Cache invalidation:** `kanbanLeadsCache` reset on `loadMore` i `updatedSearchQuery`; `kanbanCountsCache` reset tylko na search (total unchanged by pagination)

### Uzasadnienie wyboru

1. **Solo dev, alpha stage** — B i C over-engineered dla bieżącej skali. Trade-off UX minimalny (25 leadów to i tak więcej niż user widzi jednocześnie na ekranie).
2. **Livewire-native** — żadna zewnętrzna dep, prostsze debugowanie.
3. **Reversible** — jeśli realnie pojawi się scale > 100 leads/col, migracja do B lub C to odrębny ADR i iteration. Current implementation nie blokuje tej ścieżki.
4. **List view mode** — `viewMode='list'` też korzysta z `getKanbanLeads()`, więc też jest paginated. Akceptowalna konsekwencja: users potrzebujący pełnej listy do eksportu używają `LeadResource` table (pełna tabela z paginacją Filamenta + export).

## Konsekwencje

### Pozytywne

- Memory footprint stały (max 3 × 25 = 75 eager-loaded Lead records + relations per render)
- Livewire payload bounded — O(1) względem pipeline size
- User signal "lead widzisz top-25 by `created_at DESC`" — implicit recency bias, zgodny z CRM semantyką
- Test coverage: 6 feature tests (mount, cap, total count, loadMore flow, status whitelist guard, search filter)

### Negatywne

- User pracujący z kolumną > 25 leadów musi klikać "Pokaż więcej" (pierwsza page size stała — nie adaptacyjna)
- Drag-and-drop lead cards między kolumnami: jeśli target status ma > 25 leadów i dropowany lead byłby w "ukrytej" części, UX gubi go z wzroku po re-rendereu (mitigated: status badge pokazuje total)
- List view też paginated — może zaskoczyć gdy user liczy na full flat list. Mitigated: LeadResource table dla eksportów

### Known limitation (non-blocking)

- Search query filter `searchQuery` używa `LIKE %term%` — bez FULLTEXT index. D4 backlog item addresses to dla `Post.content`, Lead search pozostaje na LIKE (acceptable dla ~tysięcy leadów).

## Revisit triggers

**Migruj do B (infinite scroll) lub C (virtualization) gdy:**
- Dowolna kolumna regularnie przekracza 100 leadów (sprawdzane via monitoring alert na `Lead::inPipeline()->count()` per status)
- User feedback negatywny na "Pokaż więcej" flow (>2x/week complaints)
- Performance budget exceeded (Livewire payload > 500KB na kanban render)

Decision reopen: nowy ADR 010.1 (lub ADR 020 nowy numer) ze specific scenario-based rozwiązaniem.

## Implementation refs

- **Code:** `webfloo@c866939` — `src/Filament/Pages/CrmDashboard.php` + `resources/views/filament/pages/crm-dashboard.blade.php`
- **Tests:** `bitfloo-web@d3cc35a` — `tests/Feature/Filament/CrmDashboardKanbanPaginationTest.php` (6 tests, 13 assertions)
- **Plan:** `docs/plans/webfloo-extraction/02-extraction-plan.md` — D1 deferred item

---

**Status dokumentu:** ACCEPTED — żadne follow-up actions nie są wymagane przed produkcją alpha.
