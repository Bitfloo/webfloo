# Architectural Decision Records — webfloo

ADR-y dotyczące core package `bitfloo/webfloo`. Decyzje o architekturze aplikacji
konsumujących webfloo (np. strona firmowa bitfloo-web) siedzą w ich własnych repo.

## Format

```
# ADR-NNN: <title>

- Date: YYYY-MM-DD
- Status: PROPOSED | ACCEPTED | DEPRECATED | SUPERSEDED
- Context: <what prompted this decision>
- Decision: <what we decided>
- Alternatives: <what we considered>
- Consequences: <what follows from this decision>
```

## Lista

| # | Temat | Status |
|---|-------|--------|
| 003 | SSOT — core package jako jedyny dom modeli domeny | SUPERSEDED częściowo przez 004 |
| 004 | Full rename `bitfloo/core` → `bitfloo/webfloo` | ACCEPTED |
| 005 | Webfloo host contract (co core wymaga od aplikacji) | ACCEPTED |
| 006 | Translation strategy (plugin translation registry) | ACCEPTED |
| 007 | Feature flag matrix (ModuleRegistry) | ACCEPTED |
| 008 | Migration consolidation strategy | ACCEPTED |
| 010 | CrmDashboard kanban pagination strategy | ACCEPTED |

Numeracja zachowana z historii pre-extraction (bitfloo-web ADRs 001-002 zostały
w bitfloo-web bo dotyczą warstwy aplikacji; 009 pominięte w oryginalnej sekwencji).
