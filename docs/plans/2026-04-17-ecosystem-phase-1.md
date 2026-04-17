# SPEC: Ekosystem webfloo — Phase 1 (Distribution + Layered Skin)

**Status:** DRAFT — awaiting user GO
**Date:** 2026-04-17
**Decider:** Mike / Bitfloo (+ 3-dev team by 2026-Q3)
**Context:** Post-extraction ecosystem consolidation. Sesja kontynuacji po ADR 003-010 + webfloo@v1.0.0 + thezero@v0.1.0.
**Supersedes:** `docs/plans/_archive/webfloo-extraction/02-extraction-plan.md` (Phase 5 DONE)
**Spawns:** ADR-011 (Distribution strategy), ADR-012 (Layered skin model — planned), potencjalnie ADR-013 (Template sync protocol — Phase 3).

---

## Goal (one sentence)

Ustalić wzorzec dystrybucji i aktualizacji ekosystemu tak, aby: (1) core (`webfloo`) i base primitives (`thezero-core`) propagowały się automatycznie do wszystkich konsumentów przez semver, (2) template skin (`thezero-template`) mógł divergować per klient, ale z opcjonalną sync-skill dla kontrolowanej aktualizacji, (3) 3 devów + N klientów mogło pracować równolegle bez ślepych konfliktów.

---

## Mental model — 3 warstwy propagacji

```
┌──────────────────────────────────────────────────────────────────┐
│  WARSTWA A — CORE (auto-propagation via semver)                   │
│                                                                    │
│  webfloo (composer)              @bitfloo/thezero-core (npm)       │
│  - Models, Traits                 - Atoms (AppButton, AppIcon...)   │
│  - Filament Resources             - Composables                     │
│  - Services (ThemeService...)     - utils, lib                      │
│  - PageSettings, CRM              - Base tokens (spacing, typo)     │
│                                                                    │
│  Update flow: bug fix → tag → `composer update` / `npm update`    │
│  Konflikt ryzyko: 0 (klient nie edytuje tej warstwy)              │
└──────────────────────────────────────────────────────────────────┘
                              ▲ ▲
                              │ │
┌─────────────────────────────┴─┴────────────────────────────────┐
│  WARSTWA B — TEMPLATE (scaffold-once + sync-on-demand)           │
│                                                                   │
│  @bitfloo/thezero-template (github template repo + clone target) │
│  - Pages (Home, Blog, Portfolio, DynamicPage)                     │
│  - Layouts (LandingLayout)                                        │
│  - Sections (Hero, About, Services, FAQ, CTA, Contact, Blog...)   │
│  - Molecules (ServiceCard, ProjectCard, TestimonialCard...)       │
│  - Organisms (AppHeader, AppFooter)                               │
│  - Forms (ContactForm, NewsletterForm)                            │
│                                                                   │
│  Update flow: Agent invokes `/thezero-sync` skill → 3-way diff    │
│  Konflikt ryzyko: HIGH (klient customizuje) — sync kontroluje     │
└─────────────────────────────────────────────────────────────────┘
                              ▲
                              │
┌─────────────────────────────┴────────────────────────────────────┐
│  WARSTWA C — CLIENT (unique, fork-and-diverge)                    │
│                                                                    │
│  bitfloo-web, klient2, klient3... (Laravel apps, każdy prywatne)  │
│  - app/ (controllers, policies, specyficzne dla klienta)          │
│  - resources/js/ (forked template, customized per brand)          │
│  - content, .env, seeds, routes                                   │
│  - .thezero-sync.md (state file — last_synced, diverged_files)    │
│                                                                    │
│  Update flow: manualny (agent-driven) via                         │
│    composer update (warstwa A)                                     │
│    npm update @bitfloo/thezero-core (warstwa A)                    │
│    /thezero-sync command (warstwa B)                              │
└──────────────────────────────────────────────────────────────────┘
```

---

## Acceptance Criteria

### AC1 — webfloo dystrybuowany przez GitHub Packages Composer

- [ ] **AC1.1** `.github/workflows/publish.yml` w webfloo — trigger na tag `v*`, publish do `composer.pkg.github.com/Bitfloo`
- [ ] **AC1.2** `composer.json` w webfloo ma poprawne `name`, `type: library`, `require`; `version` field usunięty (semver tags SSOT)
- [ ] **AC1.3** Workflow `publish.yml` przetestowany na tagu `v1.0.1` (patch bump do wywołania workflow bez zmian w kodzie — tylko CHANGELOG entry)
- [ ] **AC1.4** GitHub Packages UI potwierdza webfloo@1.0.1 publikowany
- [ ] **AC1.5** Lokalny test auth: `composer.json` w sandbox directory z `auth.json` → `composer install` ściąga webfloo z Packages (nie z symlink)
- [ ] **AC1.6** ADR-011 w webfloo dokumentujący: decyzję, kryteria migration do Satis (trigger conditions), auth setup guide, rollback plan

### AC2 — bitfloo-web cutover na versioned webfloo

- [ ] **AC2.1** Branch `feat/versioned-webfloo` w bitfloo-web (izolacja zmian)
- [ ] **AC2.2** `composer.json` zmiana: `"type": "path"` → `"type": "composer"` z URL `https://composer.pkg.github.com/Bitfloo/`
- [ ] **AC2.3** `"bitfloo/webfloo": "^1.0"` zachowane (już pinned tagged version)
- [ ] **AC2.4** `composer update bitfloo/webfloo` ściąga z Packages, nie path
- [ ] **AC2.5** `make check` zielony po cutover (188 tests, 0 phpstan errors)
- [ ] **AC2.6** Smoke test: `/` 200, `/admin` 302 (nie regres)
- [ ] **AC2.7** Dev workflow doc update w CLAUDE.md: jak deweloper może wrócić tymczasowo na path repo dla local debug webfloo (`composer config repositories.webfloo path ../webfloo && composer require bitfloo/webfloo:@dev` — override lokalny, nie commit)
- [ ] **AC2.8** CI bitfloo-web .github/workflows: secret `COMPOSER_AUTH_JSON` albo `GH_PACKAGES_TOKEN` setup doc
- [ ] **AC2.9** Merge do main dopiero po pełnej weryfikacji

### AC3 — thezero split na core + template monorepo

- [ ] **AC3.1** `package.json` root thezero zmienia się w workspace config: `"workspaces": ["packages/*"]`, `"private": true`
- [ ] **AC3.2** `packages/core/` utworzony — zawiera: `Components/Atoms/`, `Composables/`, `utils/`, `lib/`, `tokens/`, `app.css` (base), `colors.css` (tokens)
- [ ] **AC3.3** `packages/core/package.json`: `name: "@bitfloo/thezero-core"`, `version: 0.1.0`, exports map na zawartość, peerDeps Vue/Inertia/Tailwind
- [ ] **AC3.4** `packages/template/` utworzony — zawiera: `Pages/`, `Layouts/`, `Components/{Molecules,Organisms,Sections,Forms}/`, `app.js` (entrypoint używający core)
- [ ] **AC3.5** `packages/template/package.json`: `name: "@bitfloo/thezero-template"`, `"private": true` (NIE publikujemy na npm — to template do clone)
- [ ] **AC3.6** Template używa `@bitfloo/thezero-core` jako dep (przez workspaces symlink lokalnie, przez npm w kliencie)
- [ ] **AC3.7** Stare `@bitfloo/thezero` v0.1.0 pozostaje na npm jako deprecated alias: README dopisek "moved to @bitfloo/thezero-core + template repo; do not install new projects"
- [ ] **AC3.8** `publish.yml` w thezero aktualizuje się: publikuje TYLKO `packages/core/` na tag `core-v*`
- [ ] **AC3.9** `ci.yml` typecheck + strukturalny check dla obu packages
- [ ] **AC3.10** README.md w root thezero tłumaczy 2-warstwową strukturę + 2 flow update'u

### AC4 — thezero jako GitHub Template Repo

- [ ] **AC4.1** Setting "Template repository" w GitHub UI dla `Bitfloo/thezero` — ON (user action, nie mogę z CLI)
- [ ] **AC4.2** README.md sekcja "Using this template" z krokami: "Use this template" button → clone → `npm install` → customize
- [ ] **AC4.3** `.github/TEMPLATE_REPOSITORY.md` — wiadomość która pokaże się po user'ach którzy clone'ują (wskazówki start)

### AC5 — bitfloo-web konsumpcja po split thezero

- [ ] **AC5.1** `package.json` bitfloo-web: `@bitfloo/thezero` → `@bitfloo/thezero-core`
- [ ] **AC5.2** `vite.config.js` update: `packageThemes` map → primitives z core, local customization z `resources/js/themes/bitfloo/`
- [ ] **AC5.3** `resources/js/themes/bitfloo/` pozostaje (to jest forked template — bitfloo-web to "special client" team builds first)
- [ ] **AC5.4** `npm run build:bitfloo` green + 1407-ish modules transformed (nie regresuje)
- [ ] **AC5.5** `.thezero-sync.md` w bitfloo-web — pierwszy state file, `last_synced: thezero@v0.1.0`, lista plików pristine vs diverged (auto-generate przez baseline diff)

### AC6 — Documentation + ADRs

- [ ] **AC6.1** **ADR-011** `011-distribution-strategy.md` w webfloo: GitHub Packages Composer vs Satis vs vcs vs private-packagist, decyzja, trigger migrations
- [ ] **AC6.2** **ADR-012** `012-layered-skin-model.md` w webfloo: dlaczego thezero-core (library) + thezero-template (scaffold), definicje warstw, scope każdej, anti-patterns
- [ ] **AC6.3** `docs/ARCHITECTURE.md` w webfloo update: 3-warstwowy model propagacji (Warstwa A/B/C), reguły "gdzie dodajesz feature" rozszerzone o core vs template split
- [ ] **AC6.4** `CLAUDE.md` w thezero update: monorepo structure, jak agenty pracują w core vs template, konwencje per package
- [ ] **AC6.5** `CLAUDE.md` w webfloo update: workflow publish (tag → workflow → Packages), auth setup dev vs CI
- [ ] **AC6.6** `CLAUDE.md` w bitfloo-web update: nowa sekcja "Consumer of" z versioned webfloo + thezero-core + forked template

### AC7 — Phase 2 + 3 planning stubs

- [ ] **AC7.1** `docs/plans/2026-04-17-phase-2-new-client-skill.md` — spec skill `/new-client` (DRAFT): kroki scaffoldingu nowego klienta
- [ ] **AC7.2** `docs/plans/2026-04-17-phase-3-thezero-sync.md` — spec skill `/thezero-sync` (DRAFT): algorytm 3-way diff, sync state file format, conflict resolution protocol
- [ ] **AC7.3** Nie implementujemy Phase 2/3 w tej iteracji — tylko dokumentujemy żeby team wiedział co przyjdzie

### AC8 — Verification + CI

- [ ] **AC8.1** Wszystkie 3 repa `make check` / `npm test` zielone po zmianach
- [ ] **AC8.2** Wszystkie CI workflows uruchamiają się i przechodzą na pull request + push do main
- [ ] **AC8.3** 0 regresji funkcjonalnych: bitfloo.com strona + admin panel działa jak przed cutover

---

## Task graph + dependencies

Oznaczenia:
- **W** = kto: Ja (AI agent w tej sesji) / User / Parallel agent
- **🔒** = blocker: blokuje kolejne kroki
- **⏱** = effort estimate: S (<30min) / M (30min-2h) / L (2-4h)
- **↺** = rollback point (commit/branch boundary)

### Track 1 — webfloo distribution (sekwencyjny)

```
T1.1  [W: Ja]    Napisz ADR-011 distribution strategy                     ⏱ M
      ↓
T1.2  [W: Ja]    Napisz .github/workflows/publish.yml w webfloo           ⏱ M
      ↓
T1.3  [W: Ja]    CHANGELOG.md bump: [1.0.1] - patch (żadnych zmian code,  ⏱ S
                 tylko distribution workflow trigger)
      ↓
T1.4  [W: Ja]    commit webfloo: "feat(dist): GitHub Packages publish
                 workflow + ADR-011"                                       ⏱ S ↺
      ↓
T1.5  [W: User]  git push webfloo main                                    🔒 S
      ↓
T1.6  [W: User]  git tag v1.0.1 && git push --tags                         🔒 S
      ↓
T1.7  [W: User]  Watch GitHub Actions → verify publish workflow OK       🔒 M
                 (or fix workflow bugs if failed)
      ↓
T1.8  [W: User]  Generate GitHub token z `read:packages` scope,          🔒 S
                 save to ~/.composer/auth.json
      ↓
T1.9  [W: Ja]    Sandbox test: temp dir `composer init` +                 ⏱ S
                 add bitfloo repo + require bitfloo/webfloo → install
```

### Track 2 — bitfloo-web cutover (zależy od T1.9)

```
T2.1  [W: Ja]    Branch feat/versioned-webfloo w bitfloo-web               ⏱ S
      ↓
T2.2  [W: Ja]    composer.json: "type": "path" → "type": "composer"       ⏱ S
                 Add GitHub Packages repo URL
      ↓
T2.3  [W: Ja]    composer update bitfloo/webfloo (wymaga T1.9 done)       ⏱ S
      ↓
T2.4  [W: Ja]    make check → expect green; fix any regression             ⏱ M
      ↓
T2.5  [W: Ja]    Smoke test: docker up + curl / + curl /admin             ⏱ S
      ↓
T2.6  [W: Ja]    commit bitfloo-web: "refactor(deps): consume webfloo
                 via GitHub Packages Composer (type: path → type: composer)" ⏱ S ↺
      ↓
T2.7  [W: User]  Review branch, push origin, merge via PR                  🔒 M
      ↓
T2.8  [W: User]  Add secret COMPOSER_AUTH_JSON do bitfloo-web GitHub       🔒 S
                 Actions (dla CI consumpcji webfloo)
```

### Track 3 — thezero monorepo split (niezależny od Track 1/2)

```
T3.1  [W: Ja]    Branch feat/monorepo-split w thezero                      ⏱ S
      ↓
T3.2  [W: Ja]    mkdir packages/core packages/template                     ⏱ S
      ↓
T3.3  [W: Ja]    git mv Components/Atoms → packages/core/Components/Atoms  ⏱ S
                 git mv Composables → packages/core/Composables
                 git mv utils → packages/core/utils
                 git mv lib → packages/core/lib
                 git mv types → packages/core/types
                 (+ app.css, colors.css → packages/core/)
      ↓
T3.4  [W: Ja]    git mv Pages → packages/template/Pages                    ⏱ S
                 git mv Layouts → packages/template/Layouts
                 git mv Components/{Molecules,Organisms,Sections,Forms}
                   → packages/template/Components/
                 (+ bootstrap.js, app.js → packages/template/)
      ↓
T3.5  [W: Ja]    Update imports w template files z "./" / relative
                 na "@bitfloo/thezero-core/..."                            ⏱ M
      ↓
T3.6  [W: Ja]    Root package.json: workspaces config + "private: true"    ⏱ S
                 Remove old npm package metadata
      ↓
T3.7  [W: Ja]    packages/core/package.json: full metadata + exports      ⏱ M
                 map + peerDeps + publishConfig (GitHub Packages)
      ↓
T3.8  [W: Ja]    packages/template/package.json: minimal + private:true    ⏱ S
                 + peerDeps on @bitfloo/thezero-core
      ↓
T3.9  [W: Ja]    pnpm install (albo npm install) → test workspaces         ⏱ M
                 symlinks działa
      ↓
T3.10 [W: Ja]    Update ci.yml: typecheck + struct check dla obu packages  ⏱ M
      ↓
T3.11 [W: Ja]    Update publish.yml: publish packages/core/ na tag         ⏱ M
                 core-v* (NIE old v* tagging)
      ↓
T3.12 [W: Ja]    README.md update — layered model tłumaczenie              ⏱ M
      ↓
T3.13 [W: Ja]    ADR-012 layered-skin-model w webfloo                     ⏱ M ↺
      ↓
T3.14 [W: Ja]    commit thezero monorepo split (single atomic commit
                 albo rozłożony — decyzja below)                           ⏱ S
      ↓
T3.15 [W: User]  git push thezero feat/monorepo-split, review, merge       🔒 M
      ↓
T3.16 [W: User]  Enable GitHub Template Repository setting                 🔒 S
      ↓
T3.17 [W: User]  git tag core-v0.1.0 → workflow publishes                  🔒 S
                 @bitfloo/thezero-core na GitHub Packages npm
      ↓
T3.18 [W: User]  Deprecate old @bitfloo/thezero@0.1.0 na npm               🔒 S
                 (npm deprecate @bitfloo/thezero "..." → see new core)
```

### Track 4 — bitfloo-web thezero consumption migration (zależy od T3.18)

```
T4.1  [W: Ja]    Branch feat/thezero-core w bitfloo-web                    ⏱ S
      ↓
T4.2  [W: Ja]    package.json: @bitfloo/thezero → @bitfloo/thezero-core    ⏱ S
      ↓
T4.3  [W: Ja]    npm install (wymaga T3.17/18 done)                        ⏱ S
      ↓
T4.4  [W: Ja]    vite.config.js: update packageThemes + aliases             ⏱ M
      ↓
T4.5  [W: Ja]    npm run build:bitfloo → expect 1407 modules, green        ⏱ S
      ↓
T4.6  [W: Ja]    Generate .thezero-sync.md baseline — diff                 ⏱ M
                 bitfloo/resources/js/themes/bitfloo/ vs
                 thezero/packages/template/ → classify files
                 pristine / diverged
      ↓
T4.7  [W: Ja]    commit bitfloo-web: "refactor(deps): thezero monorepo
                 split — consume @bitfloo/thezero-core, fork template"     ⏱ S ↺
      ↓
T4.8  [W: User]  Push + merge                                              🔒 M
```

### Track 5 — Documentation finalization (after Tracks 1-4)

```
T5.1  [W: Ja]    Update webfloo/docs/ARCHITECTURE.md — 3-layer model       ⏱ M
T5.2  [W: Ja]    Update webfloo/CLAUDE.md — publish workflow guide         ⏱ S
T5.3  [W: Ja]    Update thezero/CLAUDE.md — monorepo structure             ⏱ S
T5.4  [W: Ja]    Update bitfloo-web/CLAUDE.md — consumer status            ⏱ S
T5.5  [W: Ja]    Phase 2 spec stub (new-client skill)                      ⏱ M
T5.6  [W: Ja]    Phase 3 spec stub (thezero-sync skill)                    ⏱ M
T5.7  [W: Ja]    Commit docs-finalize                                      ⏱ S ↺
T5.8  [W: User]  Push                                                      🔒 S
```

---

## Execution strategy — kolejność rzeczywista

**Parallel tracks enabled:**
- Track 1 (webfloo distribution) — niezależne od Track 3 (thezero)
- Track 3 (thezero split) — niezależne od Track 1/2
- Track 2 depends on T1.9
- Track 4 depends on T3.18
- Track 5 depends on 1+2+3+4 done

**Zalecana sekwencja sesji (assuming parallel agents)**:
1. Session A: Track 1 (webfloo dist) — Ja robię
2. Session A+: Track 2 (bitfloo-web cutover) — Ja robię po T1.9
3. Session B (parallel): Track 3 (thezero split) — Ja (albo inny agent) robię
4. Session B+: Track 4 (bitfloo-web thezero migration) — Ja robię po T3.18
5. Session C: Track 5 (docs finalize) — Ja robię po wszystkim

Jeśli solo-session (bez parallelizacji): sekwencja T1 → T2 → T3 → T4 → T5.

---

## Risk matrix

| # | Risk | P | I | Mitigation |
|---|------|---|---|------------|
| R1 | GitHub Packages Composer registry setup errors | M | H | Workflow przetestowany małym próbnym pakietem przed webfloo cutover; fallback na `type: vcs` jeśli Packages nie działa |
| R2 | Breaking bitfloo-web during cutover | M | H | Feature branch, `make check` verify, smoke `/` + `/admin`, rollback = revert merge commit |
| R3 | Parallel agents editing same files | H | M | Każdy krok: `git fetch + git status` pre-flight; re-read files before edit |
| R4 | thezero monorepo split breaks imports | H | M | `grep -r "from '@bitfloo/thezero'"` dla lista plików do update; CI typecheck catches miss |
| R5 | Deprecated @bitfloo/thezero npm package dalej używany przez nieaktualizowane konsumenty | L | L | npm deprecate message wskaże na new; current consumer (bitfloo-web) dostaje migration w Track 4 |
| R6 | Auth token leak w commicie | L | C | Never commit `auth.json` / `.npmrc` z tokenami; gitignore sprawdzony (.npmrc już ignored w niektórych repo); use GitHub secrets dla CI |
| R7 | Bump v1.0.1 "empty" commit — semver abuse | L | L | CHANGELOG entry opisuje distribution activation jako legitimate patch; konsumenty z `"^1.0"` nic nie stracą |
| R8 | 3 devów konfliktuje na webfloo HEAD | M | M | Używamy trunk-based + PR dla większych zmian; merge queue jeśli CI slow |
| R9 | thezero-template update skill (Phase 3) nie zostanie napisany → client stuck on v0.1.0 forever | M | L | Phase 3 spec napisany → backlog dla pierwszego klienta który doświadczy problemu. Dot tego manualny `git cherry-pick` z thezero do klienta. |
| R10 | .thezero-sync.md state file konflikt między dev'ami na tym samym konsumencie | L | M | JSON schema + conflict marker protocol; sync skill regeneruje z scratch diff jeśli corrupted |

---

## Rollback strategy

Każdy Track ma punkt powrotu (↺):

| Track | Rollback command |
|---|---|
| T1 | `git revert <hash of T1.4>` w webfloo (distribution workflow — bezpieczny revert) |
| T2 | Revert merge w bitfloo-web → composer.json wraca na `type: path` |
| T3 | Revert merge w thezero → monorepo split cofnięty; old @bitfloo/thezero dalej używalny |
| T4 | Revert merge w bitfloo-web → @bitfloo/thezero ponownie jako dep |
| T5 | Revert commits docs — nic nie krytyczne |

**Globalny rollback** (wszystko źle): `git reset --hard` każdego repo na tag sprzed sesji + restore uncommitted work z backup. User MUSI zrobić backup branch przed startem (`git branch backup/pre-phase-1-20260417` w każdym repo).

---

## Decision log (w trakcie planowania)

| # | Decyzja | Alternatywy rozważone | Uzasadnienie |
|---|---------|----------------------|--------------|
| D1 | GitHub Packages Composer dla webfloo (NIE Satis, NIE vcs, NIE private-packagist) | Satis (2h setup, GH Pages), vcs (zero setup, slow), private-packagist ($) | GitHub-native, zero koszt, już auth dla thezero-core. Satis wchodzi jako P3 jeśli >5 pakietów albo CI minuty palą. |
| D2 | thezero jako monorepo (packages/core + packages/template), NIE 2 osobne repa | 2 repa (thezero-core, thezero-template), 1 repo bez split | Monorepo: atomic commits cross-package, pnpm workspaces dev simple, 1 CI pipeline. Dla 2-package scale monorepo < multi-repo overhead. |
| D3 | template = NOT npm (GitHub Template Repo) | Publish template na npm (current state) | User's model: template divergence. Update przez `npm install` wymusza przywracanie = konflikt z klientem. Template repo + agent sync skill daje kontrolę. |
| D4 | Phase 2/3 (new-client skill, thezero-sync) — NIE implementuj teraz, tylko spec | Implement wszystko w jednej sesji | Rule of Three: build abstrakcji po 3 konkretnych przykładach. Mamy 1 (bitfloo-web). Phase 2 wchodzi z 1. nowym klientem. |
| D5 | bitfloo-web = "special client" — nie używa thezero-template scaffold flow | Używa template jak każdy klient | bitfloo-web powstał PRZED split. Forked template zachowujemy (resources/js/themes/bitfloo/). Nowy klient zrobi fresh scaffold. |
| D6 | Deprecate @bitfloo/thezero@0.1.0 na npm zamiast unpublish | Unpublish całkowicie | npm 72h unpublish limit; deprecate zawsze dostępny. Message wskazuje na @bitfloo/thezero-core + template. |
| D7 | bitfloo-web cutover w feature branch + PR review | Commit prosto do main | 3 devów wkrótce = process discipline from day one |

---

## GO/NO-GO checkpoints

Przed każdym Track user potwierdza GO. Checkpoints:

- **CP1** (przed T1.1): Zgoda na ADR-011 distribution = GitHub Packages Composer
- **CP2** (przed T1.5): Ja kończę workflow + ADR + commit. User robi push + tag + smokes GitHub Actions.
- **CP3** (przed T2.1): User potwierdza że T1.9 sandbox install działa (webfloo ściąga się z Packages)
- **CP4** (przed T3.1): Zgoda na ADR-012 layered-skin-model (core + template split)
- **CP5** (przed T3.15): User reviewuje feat/monorepo-split branch + PR thezero; user merge, tag core-v0.1.0
- **CP6** (przed T4.1): User potwierdza że @bitfloo/thezero-core@0.1.0 widać w GitHub Packages
- **CP7** (przed T5.1): Wszystkie 4 Tracks done, wszystkie repo green

---

## Success metrics

Po Phase 1 zakończonej:

1. **Distribution time**: `composer install bitfloo/webfloo` z zero state (fresh klient) < 30s (teraz: n/a bo path repo)
2. **Update time**: bug fix w webfloo → klient `composer update` → fixed < 2min
3. **Test coverage**: webfloo tests/ ≥ 10 tests (obecnie 4), typecheck w thezero core + template pass
4. **Documentation**: 3× CLAUDE.md + ARCHITECTURE.md + 2× ADR = 6 artefaktów aktualnych
5. **Team readiness**: nowy dev w zespole czyta ARCHITECTURE.md + CLAUDE.md i rozumie gdzie co dodawać bez pytania

Po Phase 2 (nie ta sesja, future): pierwszy nowy klient scaffoldowany w <15min (obecnie: n/a).

Po Phase 3 (future): bug fix w thezero template → sync do wszystkich klientów <30min (obecnie: manualne cherry-pick, ~1h/klient).

---

## Out of scope (explicit)

Nie robię w tej Phase 1:

- ❌ Implementacja `/new-client` skill (Phase 2, czeka na 1. realnego klienta)
- ❌ Implementacja `/thezero-sync` skill (Phase 3, czeka na 1. client divergence bug)
- ❌ Storybook dla thezero-core components (nice-to-have, nie blocker)
- ❌ Full test suite webfloo (obecnie 4 smoke tests wystarczą; rozbudowa w kolejnych iteracjach)
- ❌ Migration portability fix (SHOW INDEX problem — ADR-013 kandydat, osobny problem)
- ❌ private-packagist setup (czekamy do >5 pakietów albo CI minutes pain)
- ❌ CLAUDE.md per katalog w thezero (Atomic Design rules — Phase 2 kiedy agent będzie scaffoldować components)
- ❌ Cleanup `resources/css/themes/{corp, minimal, qq, round, spring, test, test2}/` w bitfloo-web (osobny task, niski priorytet)
- ❌ Brand tokens SSOT refactor (kiedyś, nie teraz)

---

## Questions for user before GO

1. **Pnpm czy npm workspaces?** — pnpm lepszy dla monorepo (speed, strict deps), ale ekipa może nie mieć pnpm installed. Default: użyjemy narzędzia które jest już w `package.json` (obecnie npm).
2. **Czy mogę bumpnąć webfloo do v1.0.1 "no-op" żeby testować publish workflow?** — alternatywa: testować workflow na separate test branch z fake tag
3. **Dostęp do GitHub org**: mogę weryfikować GitHub Packages UI po Twojej stronie? (nie mogę — tylko user)
4. **Timeline**: zrobić Phase 1 w dzisiejszej sesji end-to-end (~4-6h pracy), czy rozłożyć na 2-3 dni?
5. **Przykładowy namespace klienta**: czy zakładasz że każdy klient ma własne repo pod `Bitfloo/<klient-nazwa>-web` czy ktoś inny? (ważne dla auth design)

---

## Appendix A — Plik tree po Phase 1

```
~/DEV/webfloo/                           (unchanged structure, + publish workflow)
├── .github/workflows/
│   ├── check.yml                        (exists)
│   └── publish.yml                       ← NEW (T1.2)
├── docs/
│   ├── ARCHITECTURE.md                   ← UPDATED (T5.1)
│   ├── decisions/
│   │   ├── 003-008, 010                  (exists)
│   │   ├── 011-distribution-strategy.md  ← NEW (T1.1)
│   │   └── 012-layered-skin-model.md     ← NEW (T3.13)
│   └── plans/
│       ├── 2026-04-17-ecosystem-phase-1.md  ← THIS DOC
│       ├── 2026-04-17-phase-2-new-client-skill.md  ← NEW (T5.5)
│       └── 2026-04-17-phase-3-thezero-sync.md      ← NEW (T5.6)
├── CLAUDE.md                             ← UPDATED (T5.2)
├── CHANGELOG.md                          ← UPDATED (T1.3)
├── src/                                  (unchanged)
├── tests/                                (unchanged, expanded later)
└── composer.json                         ← version field removed (T1.2)

~/DEV/thezero/                            (MONOREPO after T3)
├── .github/workflows/
│   ├── ci.yml                            ← UPDATED (T3.10)
│   └── publish.yml                       ← UPDATED (T3.11)
├── .github/TEMPLATE_REPOSITORY.md        ← NEW (T4.2)
├── packages/
│   ├── core/                             ← NEW (T3.2)
│   │   ├── package.json                  (@bitfloo/thezero-core)
│   │   ├── Components/Atoms/             (moved from root)
│   │   ├── Composables/                  (moved from root)
│   │   ├── utils/                        (moved from root)
│   │   ├── lib/                          (moved from root)
│   │   ├── types/                        (moved from root)
│   │   ├── app.css                       (base tokens — moved)
│   │   └── colors.css                    (moved)
│   └── template/                         ← NEW (T3.2)
│       ├── package.json                  (@bitfloo/thezero-template, private)
│       ├── Pages/                        (moved from root)
│       ├── Layouts/                      (moved from root)
│       ├── Components/
│       │   ├── Molecules/                (moved from root)
│       │   ├── Organisms/                (moved from root)
│       │   ├── Sections/                 (moved from root)
│       │   └── Forms/                    (moved from root)
│       ├── bootstrap.js                  (moved)
│       └── app.js                        (moved, updated imports)
├── CLAUDE.md                             ← UPDATED (T5.3)
├── README.md                             ← UPDATED (T3.12)
├── package.json                          ← MODIFIED (workspace root, private)
├── package-lock.json / pnpm-lock.yaml    (auto-updated)
└── tsconfig.json                         (unchanged)

~/DEV/bitfloo-web/                        (thin consumer after T2, T4)
├── composer.json                         ← MODIFIED (T2.2)
├── package.json                          ← MODIFIED (T4.2)
├── vite.config.js                        ← MODIFIED (T4.4)
├── .thezero-sync.md                      ← NEW (T4.6)
├── CLAUDE.md                             ← UPDATED (T5.4)
└── resources/js/themes/bitfloo/          (unchanged — this IS the forked template)
```

---

## Appendix B — ADR-011 pre-sketch (full ADR w T1.1)

```
# ADR-011 — Distribution Strategy

Status: ACCEPTED 2026-04-17
Decider: Mike / Bitfloo

Context:
- webfloo jest pakietem Composer (Laravel library)
- bitfloo-web konsumuje przez type: path (symlink do ../webfloo)
- Planujemy N klientów, 3 devów → potrzebujemy versioned distribution
- Koszt: 0 zł na razie (1 rok dev)

Decision:
GitHub Packages Composer registry (composer.pkg.github.com/Bitfloo).

Alternatives:
A. Satis + GitHub Pages — free, ale 2h setup, maintenance
B. private-packagist.com — $7/mies, official, zero infra (wybrane jeśli >5 pakietów albo Packages zawiedzie)
C. type: vcs — zero setup, ale slow clone per install, wolne CI
D. GitHub Packages Composer — GitHub-native, free, 1 auth dla 3 repo (już thezero-core tam idzie)

Chose D.

Consequences:
+ Ujednolicone auth (GH token dla Composer i npm)
+ Zero dodatkowej infra
+ Fast dist tarballs
- Mniej używane niż Satis — mniejsza community dokumentacja
- Vendor lock (GH Packages zamiast Satis Portable)

Migration triggers:
- Jeśli Packages ma outages >2h/miesiąc → migracja do Satis
- Jeśli >5 pakietów → private-packagist lub Satis
- Jeśli bardzo public open-source → Packagist.org
```

---

## Appendix C — ADR-012 pre-sketch (full ADR w T3.13)

```
# ADR-012 — Layered Skin Model (thezero)

Status: ACCEPTED 2026-04-17

Context:
- thezero ma 2 rodzaje zawartości: niezmienne primitives (AppButton) i customizable templates (Sections)
- Update flow: bug fix w primitives → propaguj do wszystkich; bug fix w templates → konflikt z klientami którzy zmieniali
- Klient divergence: template po customizacji "żyje własnym życiem"

Decision:
Split thezero na 2 warstwy:
1. @bitfloo/thezero-core (npm package, GitHub Packages) — Atoms, Composables, utils, base tokens. Updates AUTO przez `npm update`.
2. @bitfloo/thezero-template (GitHub template repo, NOT npm) — Pages, Sections, Layouts, Molecules, Organisms, Forms. Cloning na start klienta, divergence po.

Monorepo: pnpm/npm workspaces (packages/core, packages/template).

Alternatives considered:
A. 2 osobne repa — więcej nawigacji, brak atomic cross-package commits
B. 1 npm package @bitfloo/thezero (current) — update konflikt z klientem
C. Hybrid: npm dla wszystkiego + "eject" flow (jak CRA) — complex, brzydki

Chose split monorepo.

Consequences:
+ Jasna granica: co się propaguje auto vs co divergę
+ Monorepo pozwala na atomic changes (np. nowy AppIcon + Section używający go w 1 commicie)
+ Agent-friendly: `/new-client` scaffold clone's template/, `/thezero-sync` edituje tylko pristine_files
- Setup złożoność: workspaces + publish only-core workflow
- 2 tagi/wersje do manage (core-v*, brak tagu na template)

Update flow formalized:
- Core: @bitfloo/thezero-core@v1.2.0 → klient: npm update → zmiany applied
- Template: nowy commit w thezero/packages/template/ → klient: /thezero-sync skill → 3-way diff + agent decision
```

---

## Appendix D — Glossary (nowe pojęcia z tej Phase)

- **Core layer** — `@bitfloo/thezero-core` + `bitfloo/webfloo`. Automatically propagated via package managers. Klient NIE edytuje.
- **Template layer** — `@bitfloo/thezero-template`. Scaffolded once per klient, then diverges. Klient EDYTUJE.
- **Client divergence** — moment po `/new-client` scaffold gdy klient zaczyna customizować template.
- **Sync skill** — `/thezero-sync` CBC command (Phase 3). Porównuje klient-current vs template-latest vs template@last_synced, agent decyduje.
- **Sync state file** — `.thezero-sync.md` w klient repo. Track last_synced version + pristine_files vs diverged_files.
- **Pristine file** — plik template którego klient NIE zmienił. Auto-update OK.
- **Diverged file** — plik template którego klient zmienił. Sync wymaga 3-way merge + decyzji.
- **Special client** — bitfloo-web. Powstał przed split, używa `resources/js/themes/bitfloo/` jako forked template in-place zamiast fresh scaffold.

---

*End of plan. Awaiting user GO on CP1.*
