# ADR-013: Recommended host packages (suggest, not require)

- Date: 2026-06-12
- Status: ACCEPTED

## Context

Audyt przed wydaniem dla klientów wykazał trzy zdolności, których webfloo
sam nie dostarcza, a które produkcyjny host zwykle potrzebuje:

1. **2FA dla panelu admina.** Filament v5 nie ma natywnego 2FA. Panel
   chroni PII (leady, subskrybenci newslettera), więc samo hasło to za
   mało dla instalacji klienckich.
2. **Audit trail.** Gate'y `Export:Lead` / `Export:NewsletterSubscriber`
   ograniczają KTO eksportuje PII, ale nie zostawiają śladu KIEDY i CO.
   RODO-świadomi klienci potrzebują logu dostępu.
3. **Przetwarzanie obrazów.** Planowany MediaService (Phase 2) generuje
   warianty WebP przez GD; `ext-gd` nie jest dziś wymagane przez core.

## Decision

Core `require` zostaje szczupły. Trzy pozycje trafiają do `suggest`
w composer.json i są rekomendacją na poziomie hosta:

| Pakiet | Po co |
|---|---|
| `laragear/two-factor` | TOTP 2FA dla panelu (Filament v5 bez natywnego 2FA) |
| `spatie/laravel-activitylog` | Audit trail dostępu/eksportu PII (uzupełnia gate'y Export:*) |
| `ext-gd` | Warianty obrazów WebP w nadchodzącym MediaService |

Instalacja i konfiguracja pozostają decyzją hosta — webfloo wykrywa
obecność (jak przy `ext-gd` w MediaService) albo po prostu nie ingeruje.

## Alternatives

- **Bundlowanie w `require`** — odrzucone: ciężar zależności dla hostów,
  które ich nie potrzebują (np. bitfloo-web ma własne mechanizmy),
  ryzyko konfliktów wersji u klientów.
- **Pluginy Filament (np. breezy)** — odrzucone: dojrzałość ekosystemu
  pluginów pod v5 jeszcze nierówna; laragear/two-factor jest
  framework-level i nie wiąże nas z cyklem wydań pluginu.
- **Własna implementacja 2FA/activity-log w webfloo** — odrzucone:
  scope creep; to rozwiązane problemy z utrzymywanymi pakietami.

## Consequences

- `composer.json` dostaje blok `suggest` z trzema wpisami (composer
  wypisze je przy instalacji; nic nie jest wymuszane).
- README sekcja instalacyjna może odsyłać do tego ADR.
- Tematy luźno zarezerwowane wcześniej pod numer 013 (template sync,
  migration portability) przesuwają się na 014+.
