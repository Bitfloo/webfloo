# ADR 012 — Layered Skin Model (thezero: core + template split)

**Status:** ACCEPTED (2026-04-17)
**Date:** 2026-04-17
**Decider:** Mike / Bitfloo
**Context branch:** main
**Related:** ADR 005 (host contract — PHP backend), ADR 011 (distribution strategy)
**Supersedes:** thezero as single `@bitfloo/thezero` npm package (2026-04-17 initial, v0.1.0 will be deprecated)

## Context

`Bitfloo/thezero` pierwotnie wyprodukowany jako pojedynczy npm package `@bitfloo/thezero@0.1.0` publikowany na GitHub Packages, konsumowany przez `bitfloo-web` jako zwykła dependency z `package.json`.

Ten model założyłby, że klienci "instalują thezero" i dostają ten sam zestaw Pages/Sections/Components. Aktualizacje propagowałyby się przez `npm update`.

**Problem z tym modelem**: użytkownik wymaga **per-client divergence**. Każdy nowy klient dostaje bazę thezero, ale **natychmiast customizuje** Pages, Sections, Components pod swoją markę. Od tego momentu klient "żyje własnym życiem" — kolejny `npm update` nadpisałby ich customizacje albo wyprodukował merge conflicts w każdym pliku.

Jednocześnie użytkownik wymaga **update propagation** — jeśli znajdziemy bug w `AppButton.vue` (primitive używany identycznie w każdym kliencie), chcemy to naprawić u wszystkich automatycznie, bez ręcznej kopii do N repozytoriów.

Te dwa wymagania są sprzeczne dla jednego pakietu. Potrzebujemy **dwóch warstw**.

## Decision

Split `thezero` na 2 warstwy w **monorepo pnpm workspaces**:

```
thezero/
├── packages/core/       → @bitfloo/thezero-core (npm package, versioned)
└── packages/template/   → @bitfloo/thezero-template (NIE publikowany, template do clone)
```

### Warstwa 1: `@bitfloo/thezero-core` (stable, auto-propagating)

**Contains:**
- `Components/Atoms/` — AppButton, AppIcon, AppHeading, AppText, AppBadge (primitives, brand-agnostic)
- `Composables/` — useTranslations, useThemeToggle (generic React/Vue logic)
- `utils/` — slugify, techColors, formatters (pure functions)
- `lib/` — cn, cva helpers (UI utility functions)
- `types/` — shared TypeScript types
- `tokens/` — base spacing, typography scales (NOT brand colors — those w template/)
- `app.css` — base Tailwind setup, reset
- `colors.css` — **only neutral base** (gray scale, alpha helpers) — brand kolory są w template/

**Does NOT contain:**
- Sekcje — to są composable, klient może zmieniać układ
- Pages — ścieżki routes per klient
- Molecules/Organisms — np. ServiceCard zawiera brand-specific decyzje (placeholder copy, CTA)
- Layouts — per klient struktury nawigacji/footera różne
- Brand kolory — każdy klient ma swoje

**Rules:**
- Klient NIE edytuje `node_modules/@bitfloo/thezero-core/` (typowa npm dep)
- Updates przez `npm update @bitfloo/thezero-core` (albo pnpm update)
- Versioning: semver strict — breaking change w primitive = major bump = świadoma decyzja klienta o update
- Publikacja: release-please auto-flow (zgodnie z ADR-011 dla webfloo)

### Warstwa 2: `@bitfloo/thezero-template` (scaffolding, divergent)

**Contains:**
- `Pages/` — Home, Blog (Index+Show), Portfolio (Index+Show), DynamicPage
- `Layouts/` — LandingLayout
- `Components/Molecules/` — ServiceCard, ProjectCard, BlogPostCard, TestimonialCard, SectionHeader
- `Components/Organisms/` — AppHeader, AppFooter
- `Components/Sections/` — Hero, About, Services, Portfolio, Testimonials, FAQ, CTA, Contact, Blog, FeaturesGrid
- `Components/Forms/` — ContactForm, NewsletterForm
- `bootstrap.js` — app entrypoint (import `@bitfloo/thezero-core`, setup)
- `app.js` — Inertia/Vue app bootstrap

**Package.json**: `"private": true` — **NIE publikujemy** na npm. Źródło to GitHub Template Repository.

**Klient start flow**:
```
1. "Use this template" w GitHub UI (Bitfloo/thezero-template)
   → nowy repo Bitfloo/klient-X-web (fresh bez historii)
2. Clone + customize package.json (nazwa pod klienta)
3. pnpm install → ściąga @bitfloo/thezero-core + Vue + Tailwind
4. Agent (Phase 2 /new-client skill) aplikuje brand tokens
5. Klient żyje własnym życiem; template nie jest już "dep"
```

### Warstwa 3: update propagation dla template (Phase 3)

**Problem**: bug w `Sections/Hero.vue` — chcemy naprawić u wszystkich klientów którzy jeszcze NIE customizowali tej sekcji.

**Solution** (Phase 3, osobny ADR-013 planowany): CBC skill `/thezero-sync`:

1. **Stan tracking**: każdy klient ma `.thezero-sync.md` z:
   ```yaml
   last_synced: "thezero-template@<commit-sha-at-scaffold>"
   files:
     pristine: [Pages/Portfolio/Show.vue, Sections/FAQ.vue, ...]
     diverged: [Pages/Home.vue, Sections/Hero.vue, ...]  # + first_diverged_at per file
   ```

2. **Sync algorithm**:
   ```
   for each file in pristine_files:
     3-way-diff(
       base = thezero-template@last_synced:file,
       current = client:file,
       target = thezero-template@HEAD:file
     )
     if diff(base, current) == 0:   # klient nie tknął
       → auto-apply target
     else:
       → mark as diverged, skip

   for each file in diverged_files:
     3-way diff
     → show to agent
     → agent decides: apply / skip / merge
     → log decision w .thezero-sync.md
   ```

3. **Result**: selective update, kontrola lokalna, audit trail, idempotent.

## Alternatives considered

| Opcja | Opis | Odrzucone bo |
|-------|------|--------------|
| **Jeden npm package** (initial v0.1.0) | `@bitfloo/thezero` zawiera wszystko, klient `npm install` i `npm update` | `npm update` nadpisałoby klienta customizacje. Każdy Pages/Sections w node_modules → klient nie może edytować bezpośrednio |
| **Monorepo 2 repa** (thezero-core, thezero-template) | Osobne GitHub repa | Utrata atomic commits cross-package (np. nowy AppIcon + Section używający go w 1 merge). 2× setup, 2× CI. Overhead większy niż benefit. |
| **Eject flow** (jak create-react-app) | Klient startuje z npm dep; w pewnym momencie "ejectuje" kopiując pliki z node_modules | Brzydkie transition, brak jasnej linii — klient może część ejectować, część nie, chaos. |
| **Git submodules** | Klient ma thezero jako submodule | Git submodules są uczuleniem devów; konflikty co update; agent nie obsłuży prosto. |
| **Pojedynczy GitHub Template Repo bez core split** | Wszystko jako template, `npm install` tylko Vue/Tailwind | Brak auto-propagation primitives. Bug w AppButton → manualna kopia do N klientów. |

## Consequences

### Pozytywne

- **Jasna semantyka**: primitive (core) vs custom (template) = klient wie co może edytować, co dostaje automatycznie
- **Agent-friendly**: scaffold = `git clone` + `npm install`; update primitive = `npm update`; update template = `/thezero-sync` skill
- **Monorepo atomic**: nowy Icon + Section używający go w 1 commit
- **Publish isolation**: tylko core jest publikowany; template to artefakt repo, nie pakiet
- **Divergence respected**: klient customizuje template as much as he wants bez konfliktu z updatami core
- **Rollback simple**: klient może pinować starszą wersję core (`"^0.3"`) niezależnie od stanu swojego template
- **Sync state explicit**: `.thezero-sync.md` w kliencie = audit trail decyzji "apply / skip" per plik

### Negatywne

- **Setup złożoność**: pnpm workspaces + 2 package.json + osobny publish workflow (tylko core)
- **Sync skill złożoność**: Phase 3 wymaga napisania `/thezero-sync` z 3-way diff logic + agent integration
- **Discipline wymagana**: dev musi wiedzieć czy dodaje Atomic (core) czy Section (template). ADR precyzuje ale błąd w klasyfikacji = przyszły ból.
- **2 artefakty do maintain**: core versioning + template GitHub Template settings
- **Test setup**: tests w core (unit Vitest) vs template (integration + storybook?) — osobne pipelines

## Implementation sketch

### Files / directory tree po split

```
~/DEV/thezero/
├── .github/workflows/
│   ├── ci.yml              (typecheck + struct check obie packages)
│   ├── release.yml         (release-please auto bump core)
│   └── publish.yml         (publish core na release)
├── packages/
│   ├── core/
│   │   ├── package.json    (@bitfloo/thezero-core, publish: restricted)
│   │   ├── Components/Atoms/
│   │   ├── Composables/
│   │   ├── utils/
│   │   ├── lib/
│   │   ├── types/
│   │   ├── tokens/
│   │   ├── app.css         (base Tailwind)
│   │   └── colors.css      (neutral scale)
│   └── template/
│       ├── package.json    (@bitfloo/thezero-template, private: true)
│       ├── Pages/
│       ├── Layouts/
│       ├── Components/
│       │   ├── Molecules/
│       │   ├── Organisms/
│       │   ├── Sections/
│       │   └── Forms/
│       ├── bootstrap.js
│       └── app.js
├── package.json            (workspace root, private: true, workspaces: ["packages/*"])
├── pnpm-workspace.yaml     (pnpm workspace config)
├── pnpm-lock.yaml          (replace package-lock.json)
├── README.md               (explain layered model + flow: use template → install core)
└── .github/TEMPLATE_REPOSITORY.md  (onboarding message dla klonujących)
```

### Import mapping (template uses core)

```js
// packages/template/Pages/Home.vue
import { AppButton, AppHeading } from '@bitfloo/thezero-core'
import { useTranslations } from '@bitfloo/thezero-core/composables'
```

pnpm workspace link tworzy symlink `packages/template/node_modules/@bitfloo/thezero-core` → `../packages/core` podczas dev. Po clone przez klienta (nie workspace) → prawdziwa npm install.

### Migration flow z obecnego stanu

```
1. Current: thezero/ ma wszystko w src/ jako @bitfloo/thezero@0.1.0 (npm published)
2. Commit: git mv src/Components/Atoms → packages/core/Components/Atoms (i reszta)
3. Commit: workspace configs
4. Commit: package.json updates per package
5. Commit: update imports w template (@bitfloo/thezero-core paths)
6. Tag: core-v0.1.0 (release-please weźmie stamtąd)
7. Deprecate @bitfloo/thezero@0.1.0 na npm: `npm deprecate @bitfloo/thezero@0.1.0 "moved to @bitfloo/thezero-core — see repo README"`
```

## Verification

- [ ] `packages/core/` + `packages/template/` istnieją z poprawną zawartością
- [ ] `pnpm install` w root działa, symlinks OK
- [ ] `packages/core/` ma `package.json` z poprawnym `name`, `version`, `exports`, `peerDependencies`
- [ ] `packages/template/` ma `"private": true` i peerDep na `@bitfloo/thezero-core`
- [ ] Imports w template zaktualizowane na `@bitfloo/thezero-core/...`
- [ ] Build działa: `pnpm --filter @bitfloo/thezero-core build` (jeśli core ma build step) albo `tsc --noEmit` dla typecheck
- [ ] CI typecheck green dla obu packages
- [ ] GitHub Template Repository setting ON dla `Bitfloo/thezero`
- [ ] `README.md` tłumaczy 2-warstwowy model
- [ ] bitfloo-web migracja na `@bitfloo/thezero-core` zamiast `@bitfloo/thezero` — `npm run build:bitfloo` green, 1407-ish modules bez regresji
- [ ] Stary `@bitfloo/thezero@0.1.0` deprecated na npm z wskazówką gdzie szukać
