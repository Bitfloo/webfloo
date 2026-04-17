# SPEC: Phase 2 — `/new-client` CBC skill

**Status:** DRAFT — spec only (not implemented this session, per Rule of Three)
**Date:** 2026-04-17
**Decider:** Mike / Bitfloo
**Trigger:** pierwszy prawdziwy nowy klient (nie bitfloo-web) wymagający scaffolding
**Related:** ADR-011 (distribution), ADR-012 (layered skin), Phase 1 plan

## Goal

CBC skill `/new-client <nazwa>` scaffolduje nowe repo klienta wykorzystujące ekosystem webfloo + thezero-core + thezero-template fork, aplikuje brand tokens, tworzy pierwszy commit. Zero ręcznej konfiguracji.

## Acceptance Criteria

### AC1 — Inputs

- [ ] **AC1.1** Command: `/cbc:new-client <client-slug>` (np. `/cbc:new-client acme`)
- [ ] **AC1.2** Interactive prompts (albo flags):
  - Brand name (display): `--name "ACME Industries"`
  - Primary kolor: `--primary "#dc2626"`
  - Secondary kolor: `--secondary "#1a1a1a"`
  - Font family: `--font "Inter"`
  - Repo owner: `--owner Bitfloo` (default)
  - Laravel backend: `--backend yes|no` (default yes)
- [ ] **AC1.3** Validation — slug = lowercase a-z0-9 + hyphens, unique w org

### AC2 — Scaffold flow

- [ ] **AC2.1** Utworzenie nowego repo w GH org (wymaga `gh` CLI auth z `repo` scope):
  - `gh repo create Bitfloo/<slug>-web --private --template Bitfloo/thezero`
- [ ] **AC2.2** Clone do `~/DEV/<slug>-web/`
- [ ] **AC2.3** Remove template-only files:
  - Delete `packages/core/` (klient użyje z npm, nie monorepo)
  - Move `packages/template/src/*` na `resources/js/themes/<slug>/` (Laravel konwencja)
  - Delete `packages/`, workspace configs
  - Remove `.github/TEMPLATE_REPOSITORY.md`, `packages/template/package.json`
- [ ] **AC2.4** Generate Laravel app (jeśli `--backend yes`):
  - `composer create-project laravel/laravel . "^12.0"` (override w tym samym dir)
  - Merge with existing files z template (zachowaj theme assets)
- [ ] **AC2.5** Configure composer.json:
  - Add `"type": "vcs"` repo dla webfloo
  - Add `"bitfloo/webfloo": "^0.1"` require
  - Add `COMPOSER_AUTH` env var dokumentacja w README
- [ ] **AC2.6** Configure package.json:
  - Add `.npmrc` z `@bitfloo:registry=https://npm.pkg.github.com`
  - Add `"@bitfloo/thezero-core": "^0.1"` dep
  - Update scripts: `dev:<slug>`, `build:<slug>`

### AC3 — Brand application

- [ ] **AC3.1** Edit `resources/js/themes/<slug>/colors.css`:
  - Replace zero default black/red z `--primary`, `--secondary`
- [ ] **AC3.2** Edit `resources/js/themes/<slug>/app.css`:
  - Inject font family z `--font` (Google Fonts albo user-provided)
- [ ] **AC3.3** Update `resources/js/themes/<slug>/Organisms/AppHeader.vue` z brand name
- [ ] **AC3.4** Update `resources/js/themes/<slug>/Organisms/AppFooter.vue` z brand name
- [ ] **AC3.5** Update `resources/js/themes/<slug>/Pages/Home.vue` Hero section z `--name`

### AC4 — Initial state files

- [ ] **AC4.1** `.thezero-sync.md` — snapshot current state vs thezero@HEAD:
  ```yaml
  last_synced:
    thezero: <commit-sha of Bitfloo/thezero@HEAD>
    date: 2026-XX-XX
  files:
    pristine:
      - resources/js/themes/<slug>/Sections/FAQ.vue
      - resources/js/themes/<slug>/Sections/CTA.vue
      - ... (files NOT modified przez brand apply)
    diverged:
      - resources/js/themes/<slug>/colors.css (customized for brand)
      - resources/js/themes/<slug>/Organisms/AppHeader.vue (brand name)
      - ... (files modified przez brand apply)
  ```
- [ ] **AC4.2** `CLAUDE.md` — ekosystem pointer + `<slug>`-specific rules
- [ ] **AC4.3** `.env.example` z poprawnymi defaults

### AC5 — Git state

- [ ] **AC5.1** Pierwszy commit: `chore: initial scaffold for <slug>`
- [ ] **AC5.2** Push main z CI running
- [ ] **AC5.3** Verify CI green (Laravel test + npm typecheck)

### AC6 — Agent verification

- [ ] **AC6.1** `composer install` works (z COMPOSER_AUTH)
- [ ] **AC6.2** `pnpm install` works (z `.npmrc` auth)
- [ ] **AC6.3** `php artisan migrate` works (creates webfloo tables)
- [ ] **AC6.4** `pnpm run dev` works → Vite serves correctly
- [ ] **AC6.5** `/` route returns 200 z brand colors applied

## Technical design

### Skill location

`~/.claude/plugins/cache/bitfloo-cbc/cbc/X.Y.Z/skills/new-client/SKILL.md`

### Skill inputs/outputs

```yaml
inputs:
  slug: string (required, slug-format)
  name: string (optional, default = titlecase slug)
  primary: hex-color (default #0a0a0a)
  secondary: hex-color (default #737373)
  font: google-font-name (default "Inter")
  backend: boolean (default true)
  owner: github-org (default "Bitfloo")

outputs:
  repo_url: string (GitHub repo URL)
  local_path: string (~/DEV/<slug>-web)
  first_commit_sha: string
  ci_run_id: string
  verified: boolean (AC6 pass)
```

### Implementation plan (sketch)

```
1. Parse inputs + validate (slug uniqueness via gh api)
2. Create repo z template (gh repo create --template)
3. Clone local
4. Delete core/, move template/src → resources/js/themes/<slug>/
5. composer create-project laravel/laravel (merge)
6. Inject bitfloo/webfloo dep + auth setup
7. Inject @bitfloo/thezero-core dep + .npmrc
8. Apply brand tokens (sed-based color replace)
9. Generate .thezero-sync.md
10. Write CLAUDE.md
11. git add + commit + push
12. Wait for CI (gh run watch)
13. Verify AC6 (run composer install, pnpm install, php artisan, pnpm run build)
14. Report success + URLs
```

### Dependencies (runtime)

- `gh` CLI auth (repo scope)
- GitHub PAT z `read:packages`
- Docker (dla smoke test backend)
- pnpm
- composer

### Error handling

- Slug conflict → fail early before repo create
- Template clone fail → retry 3x, then abort z clean up
- Brand apply fail → rollback (git reset) + report which file
- CI fail → don't remove repo, report run_id dla dev debugging

## Out of scope (Phase 2)

- UI designer dla klienta (brand tokens przez argumenty albo prompt)
- Multi-locale setup (dodajemy w Phase 2.1)
- Custom Laravel modules (każdy klient dodaje po scaffold)
- Deployment (osobna Phase — 2.2 deploy templates)

## Triggers dla implementacji

**Zacznij pisać skill gdy**:
- Przyszedł pierwszy realny klient (nie bitfloo-web)
- Masz 2+h czas na iteracje (skill wymaga testowania end-to-end)
- Flow manualny zrobiłeś już przynajmniej raz (żeby wiedzieć gdzie są grabcie)
