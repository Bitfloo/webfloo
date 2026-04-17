# ADR 011 — Distribution Strategy + Release Automation

**Status:** ACCEPTED (2026-04-17)
**Date:** 2026-04-17
**Decider:** Mike / Bitfloo (for 3-dev team + N clients roadmap)
**Context branch:** main
**Related:** ADR 005 (host contract), ADR 012 (layered skin model)
**Supersedes:** path-repo consumption pattern used during extraction (implicit default)

## Context

Po ekstrakcji core z `bitfloo-web` do standalone repo `Bitfloo/webfloo`, pakiet konsumowany przez `bitfloo-web` wyłącznie przez `composer.json type: "path"` (symlink do `../webfloo`). Setup zadziałał dla 1 consumera + 1 deva, ale nie skaluje się pod:

- **3 devów** pracujących równolegle na webfloo (PR review, version bump coordination, atomic release cycles)
- **N klientów** (target: roadmap do 5 w ciągu 2026) każdy robiący `composer install` / CI minutes
- **Deterministic deploy**: produkcja musi konsumować konkretną wersję, nie snapshot symlinka
- **Versioned update story**: "bug w webfloo → fix v0.2.3 → propagacja do wszystkich klientów"

Musimy wybrać (a) mechanizm dystrybucji Composer i (b) proces release'u (ręczny vs automatyczny). **Budget infrastruktury: 0 zł przez pierwsze 12 miesięcy rozwoju.**

## Decision

### 1. Distribution: **Composer `type: vcs` — bezpośrednie źródło git, zero extra infra**

GitHub **NIE obsługuje** natywnie rejestru Composer w GitHub Packages (w przeciwieństwie do npm, Docker, Maven, NuGet, RubyGems). Dwa realistycznie darmowe sposoby dystrybucji Composer:

**(A) `type: vcs`** — Composer klonuje repo git po tagu, używa `composer.json` jako manifestu. **Wybrane.**
**(B) Satis + GitHub Pages** — statyczny rejestr Composer generowany przez CI, dist tarballe cache'owane. Rozważone jako future migration.

Konfiguracja w konsumencie:

```json
// composer.json (bitfloo-web, future clients)
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/Bitfloo/webfloo.git"
    }
  ],
  "require": {
    "bitfloo/webfloo": "^0.1"
  }
}
```

Auth (lokalnie — private repo access):

```json
// ~/.composer/auth.json (NIGDY w repo, NIGDY w dockerfile)
{
  "github-oauth": {
    "github.com": "<github-pat-repo-read>"
  }
}
```

Token scope: **`repo`** (minimal: `repo:public_repo` gdyby webfloo był publiczny — NIE JEST, więc pełny `repo` do private access).

Auth (CI, w GitHub Actions konsumenta):

```yaml
env:
  COMPOSER_AUTH: |
    {
      "github-oauth": {
        "github.com": "${{ secrets.GH_PACKAGES_TOKEN }}"
      }
    }
```

Tworzenie tokenu: `github.com/settings/tokens` → Generate (classic PAT) → scopes: `repo` → save pod nazwą `bitfloo-ecosystem-composer-auth`.

**Dlaczego NIE `type: path`**: symlink działa tylko dla 1 deva na jednym hoście. CI konsumenta nie ma dostępu do `../webfloo/`. Deploy produkcyjny nie ma. Non-starter dla multi-dev + multi-client.

**Dlaczego NIE `type: composer` z GitHub Packages URL (moja pierwotna propozycja)**: GitHub Packages nie wspiera Composer natively. Próbowanie pointowania `type: composer` na `composer.pkg.github.com` zwróci 404.

**Dlaczego NIE Packagist.org**: wymaga public repo; webfloo jest private (proprietary, ADR o widoczności).

### 2. Versioning: **Semver 0.x (pre-stable) → 1.0 gdy produkcja stabilna**

Webfloo w fazie aktywnego rozwoju — API się zmienia, klientów jeszcze nie ma. `0.x` semver oznacza honestnie: "działa, ale breaking changes mogą się zdarzyć". Start: **v0.1.0**.

**Trigger bump do 1.0.0**:
- Minimum 2 produkcyjni konsumenci, lub
- Stable API przez minimum 30 dni bez `BREAKING CHANGE:` commitów, lub
- Świadoma decyzja team po review CHANGELOG

**Obecny v1.0.0**: BĘDZIE USUNIĘTY z remote (destructive, zero external consumers — `bitfloo-web` używa path symlinka, nie tagu). User ręcznie:
```bash
git tag -d v1.0.0 && git push origin :refs/tags/v1.0.0
```

Następnie release-please bierze over i zaczyna od v0.1.0 po pierwszym `feat:` commicie.

### 3. Release automation: **release-please (Google's)**

Konwencja: **Conventional Commits**. Każdy commit na main zawiera prefix:

| Prefix | Bump w 0.x | Po 1.0 |
|--------|-----------|--------|
| `feat:` | minor (0.1.0 → 0.2.0) | minor |
| `fix:` | patch (0.1.0 → 0.1.1) | patch |
| `feat!:` lub `BREAKING CHANGE:` w body | minor (respecting pre-major semver) | major |
| `docs:`, `chore:`, `refactor:`, `test:`, `ci:` | żaden | żaden |

**Konfiguracja** (`.github/release-please-config.json`):
```json
{
  "release-type": "php",
  "packages": {
    ".": {
      "package-name": "bitfloo/webfloo",
      "bump-minor-pre-major": true,
      "bump-patch-for-minor-pre-major": false
    }
  }
}
```

**Flow**:
1. Dev pushuje commit na main (np. `fix: sitemap null guard`)
2. Workflow `release.yml` uruchamia `googleapis/release-please-action@v4`
3. Action analizuje commity od last tag
4. Jeśli `feat:` / `fix:` bez aktywnego release PR → tworzy (lub update) **Release PR** z:
   - aktualizacją `CHANGELOG.md` (grouped per type)
   - update wersji w `.github/release-please-manifest.json`
   - title typu "release: 0.2.0"
5. Inny dev (albo ten sam) review + merge Release PR
6. Release-please auto-taguje `v0.2.0` + tworzy GitHub Release
7. Konsumenci z `"^0.2"` dostają nową wersję przy następnym `composer update`

**To jest "simple automatic bump" który wymagałeś** — manualny krok = tylko merge Release PR (świadoma decyzja kiedy released).

### 4. Distribution moment dla `type: vcs`

Z `type: vcs`, moment dystrybucji = moment tagowania. Nie ma osobnego workflow "publish" — Composer czyta tagi bezpośrednio z git. Brak `publish.yml` w webfloo (w przeciwieństwie do thezero-core, które idzie do GitHub Packages npm gdzie publish to osobny krok).

Release-please tworzy tag → natychmiast dostępny dla `composer update`. Brak opóźnienia, brak dodatkowych workflows.

### 5. Rollback

Jeśli v0.3.0 wprowadzi regresję u konsumenta:
- Konsument pinuje `"bitfloo/webfloo": "0.2.*"` tymczasowo
- Core team robi `fix:` commit na main → release-please proponuje v0.3.1
- Konsument wraca na `"^0.3"`

**NIE robimy** `git push --force` na tagach opublikowanych (destructive, może wybuchnąć u klientów którzy już `composer install`).

### 6. Future migration → Satis + GitHub Pages

**Trigger warunki** (dowolne z nich wystarczy aby rozważyć):

| Warunek | Symptom | Kiedy przejść |
|---------|---------|---------------|
| `composer install` > 30s | Wolne CI bitfloo-web albo klientów | >3 klientów + 2min+ na install |
| `git clone` minutes w CI | Minutes = money (przy własnych runnerach) | Budżet CI palony na clones |
| GitHub rate limit errors | `X-RateLimit-Remaining: 0` w logach | Rzadkie, ale może wystąpić przy N klientach x parallel CI |
| >3 publikowanych pakietów Composer | Aggregacja w 1 miejscu wygodniejsza niż N × vcs | Gdy dodamy np. `bitfloo/webfloo-crm`, `bitfloo/webfloo-blog` |

**Satis setup** (gdy trigger odpali, osobny ADR):
1. Nowy repo `Bitfloo/composer-registry`
2. `satis.json` listuje private repo webfloo (+ future packages)
3. `satis build` generuje statyczny site (dist tarballe + index)
4. GH Actions workflow uruchamia build na tag push dowolnego pakietu
5. GH Pages serwuje jako `https://composer.bitfloo.io/` (custom domain albo `bitfloo.github.io/composer-registry`)
6. Konsumenci: `"type": "composer", "url": "https://composer.bitfloo.io/"`

**Przewidywany effort migracji**: 2-3h + testy + PR dla każdego konsumenta (composer.json update).

## Alternatives considered

| Opcja | Koszt | Setup | Odrzucone bo |
|-------|-------|-------|--------------|
| **`type: vcs`** ✅ | 0 zł | 2 min | WYBRANE (argumenty wyżej) |
| **Satis + GitHub Pages** | 0 zł | 2h | Extra infra maintenance zanim potrzeba. Rozważymy przy triggerach powyżej |
| **private-packagist.com** | ~$7/mies | 15 min | Koszt w okresie rozwoju 0-revenue. Rozważymy gdy ekipa rośnie + klientów >5 |
| **GitHub Packages Composer** | 0 zł | n/a | **NIE ISTNIEJE** natywnie. Potwierdzone: GitHub Packages obsługuje npm/Docker/Maven/NuGet/RubyGems, nie Composer |
| **Packagist.org** (public) | 0 zł | 5 min | Wymaga public repo — narusza proprietary license webfloo |
| **`type: path`** (current) | 0 zł | 0 min | Nie działa dla CI/prod/nowych devów (symlink local only) |
| **Manualny `git tag` bump** | 0 zł | 0 min | User wprost poprosił o auto-bump. Human error-prone przy 3 devach |
| **semantic-release** | 0 zł | 30 min | Node.js dep dla PHP repo = niezgrabne. release-please jest GitHub-native i ma PHP release-type |
| **changesets** | 0 zł | 30 min | npm-focused, słabe wsparcie Composer |

## Consequences

### Pozytywne

- **Zero infra**: brak Satis do utrzymania, brak rejestru do rebuild'owania
- **Zero dodatkowego kosztu**: tylko GitHub (już używamy)
- **1 narzędzie dla całego ekosystemu**: release-please obsługuje zarówno Composer (webfloo) jak i npm (thezero-core przez `release-type: node` w osobnym repo)
- **Zero manualnego tagowania**: dev commituje z konwencją, release PR się sam buduje
- **CHANGELOG auto-generated**: nie zapomnimy wpisu, historia consumer-friendly
- **Atomic distribution per tag**: każdy merge Release PR → tag → konsumenci widzą natychmiast
- **Conventional commits edukuje team**: 3 devów uczy się disciplined commit messages (zysk out-of-band)

### Negatywne

- **`composer install` slower niż Satis** (clone git historii vs dist tarball): akceptowalne przy <5 klientach × <10 install/dzień
- **Conventional commits discipline wymagana**: lokalny commitlint hook zarekomendowany (wdrożymy w Phase 1 kontynuacji)
- **GitHub Packages npm + Composer vcs = 2 różne auth flows**: npm uses `.npmrc` z GitHub Packages, Composer uses `auth.json` z github-oauth. Oba używają tego samego GitHub token, ale różna konfiguracja per konsument.
- **Nie można łatwo delisting buggy version**: raz tag opublikowany, tylko `yank` przez CHANGELOG note + forward fix. Mitigated: strict testing + CI przed merge do main.

## Implementation sketch

### Files utworzone w tej zmianie

```
.github/release-please-manifest.json     — tracker wersji
.github/release-please-config.json       — config release-please (release-type: php)
.github/workflows/release.yml            — trigger release-please na push main
docs/decisions/011-distribution-strategy.md  ← THIS FILE
```

### composer.json changes (webfloo)

```diff
- "version": "1.0.0",      # removed — tagi z git są SSOT (release-please bumps package-name in manifest)
```

### CHANGELOG.md strategy

Istniejący `CHANGELOG.md` z wpisem `[1.0.0] - 2026-04-01` zostanie **przeniesiony do sekcji "Pre-release history (manual)" na dole pliku**, a release-please zacznie prowadzić nowy format od v0.1.0. Nie usuwamy historii, oznaczamy ją jako legacy.

### Consumer-side changes (bitfloo-web + przyszli klienci)

**bitfloo-web** (Track 2 tego sprintu):
```diff
  "repositories": [
      {
-         "type": "path",
-         "url": "../webfloo",
-         "options": {
-             "symlink": true
-         }
+         "type": "vcs",
+         "url": "https://github.com/Bitfloo/webfloo.git"
      }
  ],
  "require": {
-     "bitfloo/webfloo": "^1.0",
+     "bitfloo/webfloo": "^0.1",
```

**Nowi klienci**: identyczna konfiguracja + `auth.json` z GitHub PAT.

### CI adjustment dla bitfloo-web

`.github/workflows/` w bitfloo-web muszą dodać:

```yaml
env:
  COMPOSER_AUTH: |
    {
      "github-oauth": {
        "github.com": "${{ secrets.GH_PACKAGES_TOKEN }}"
      }
    }
```

**Secret** `GH_PACKAGES_TOKEN` ustawiany ręcznie przez ownera repo w Settings → Secrets.

### Local dev workflow dla deva webfloo

Dev który aktywnie hacks na webfloo + chce natychmiastowy feedback w bitfloo-web może tymczasowo override'ować `composer.json` konsumenta:

```bash
cd ~/DEV/bitfloo-web
composer config repositories.webfloo path ../webfloo  # override lokalny
composer update bitfloo/webfloo  # symlinkuje
# ...dev iteruje...
composer config --unset repositories.webfloo  # restore vcs
composer update bitfloo/webfloo
```

**Nie commitujemy** tego override'u. Dokumentowane w CLAUDE.md.

## Verification

Po wdrożeniu Track 1 (Distribution) tego spec'u:

- [ ] Stary tag `v1.0.0` usunięty z remote (destructive, zero external consumers)
- [ ] `.github/workflows/release.yml` + release-please configs istnieją
- [ ] `composer.json` webfloo ma usunięte `version` field
- [ ] Pierwszy `feat:` albo `fix:` commit → release-please tworzy Release PR
- [ ] Merge Release PR → tag `v0.1.0` + GitHub Release
- [ ] Sandbox: osobny katalog `mkdir /tmp/webfloo-test && cd $_ && composer init` z dodanym `type: vcs` repo + `composer require bitfloo/webfloo:^0.1` — install OK z `auth.json` setup
- [ ] bitfloo-web cutover: `"type": "path"` → `"type": "vcs"`, `composer update`, `make check` green

## Migration triggers (future ADR revisits)

| Warunek | Sygnał | Next step |
|---------|--------|-----------|
| `composer install` > 30s na CI | Slow feedback | Satis migration (osobny ADR) |
| >3 klientów konsumuje | Multiplikacja `git clone` kosztów | Satis lub GitHub Container Registry (docker base images pattern) |
| GitHub rate limit hits | CI logs `429 Too Many Requests` | Authenticated user gets 5k/h; może wystarczyć, jeśli nie — Satis |
| Publikujemy >2 pakiety Composer | `bitfloo/webfloo-crm`, `bitfloo/webfloo-blog` etc | Satis bo agreguje; vcs wymaga N repo URL |
| Klient idzie open-source | `public` repo | Packagist.org (free, automatyczna indeksacja) |
| Musimy deprekować version | Security advisory | Satis daje `exclude` w `satis.json`; vcs nie ma tego mechanizmu — tylko CHANGELOG note |
