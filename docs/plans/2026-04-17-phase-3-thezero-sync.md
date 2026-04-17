# SPEC: Phase 3 — `/thezero-sync` CBC skill

**Status:** DRAFT — spec only (not implemented this session)
**Date:** 2026-04-17
**Decider:** Mike / Bitfloo
**Trigger:** pierwszy bug fix w `thezero/packages/template/` który chcemy rozpropagować do klienta
**Related:** ADR-012 (layered skin), Phase 2 (new-client — creates `.thezero-sync.md`)

## Goal

CBC skill `/thezero-sync` aktualizuje forked template u klienta na podstawie zmian w `Bitfloo/thezero/packages/template/`. Respektuje klienta customizacje (diverged files) + automatycznie propaguje bez-konfliktowe fixes (pristine files). Audit trail w `.thezero-sync.md`.

## Goal in one sentence

"Wymuś mi update template thezero, ale nie zepsuj mojej pracy."

## Acceptance Criteria

### AC1 — Inputs

- [ ] **AC1.1** Command: `/cbc:thezero-sync` (no args — state z `.thezero-sync.md`)
- [ ] **AC1.2** Optional flags:
  - `--dry-run` — pokaż co zmieni, nic nie robi
  - `--target-commit <sha>` — sync do konkretnego commita thezero zamiast HEAD
  - `--force-pristine` — traktuj wszystkie pristine files jako safe-apply (no confirmation)
  - `--filter <glob>` — tylko te pliki (np. `--filter "Sections/*"`)
- [ ] **AC1.3** Run tylko w klientskim repo (detect: has `.thezero-sync.md`)

### AC2 — State file `.thezero-sync.md`

Format:

```yaml
---
schema_version: 1
thezero_repo: https://github.com/Bitfloo/thezero
template_path_in_thezero: packages/template/src
template_path_in_client: resources/js/themes/<slug>
last_synced:
  commit: <sha>
  date: 2026-XX-XX
  by: <user>
file_registry:
  pristine:
    - path: Sections/FAQ.vue
      hash_at_sync: <sha256>  # track jeśli klient potem modified
    - path: Sections/CTA.vue
      hash_at_sync: <sha256>
    ...
  diverged:
    - path: Pages/Home.vue
      diverged_at: 2026-XX-XX
      reason: brand customization
    - path: Organisms/AppHeader.vue
      diverged_at: 2026-XX-XX
      reason: logo replacement
  skipped_during_sync:
    - path: Molecules/NewWidget.vue
      synced_from: commit-sha
      decision: skip-apply (user opted out)
sync_history:
  - commit: <old-sha>
    date: 2026-XX-XX
    applied: [Sections/FAQ.vue (pristine auto-apply)]
    skipped: []
    conflicts_resolved: [Sections/Hero.vue (3-way merge, took both)]
---

# thezero sync log

Human-readable changelog dzień po dniu.
```

- [ ] **AC2.1** Schema validated przy každym sync
- [ ] **AC2.2** Jeśli missing → fail z komunikatem "Nie jest to klient webfloo zescaffoldowany — uruchom /new-client najpierw"

### AC3 — Sync algorithm

```
1. CLONE thezero@HEAD do /tmp/thezero-sync-<timestamp>
2. READ .thezero-sync.md → state
3. DETECT unannounced divergence:
   for each file in pristine registry:
     if hash(client_file) != state.hash_at_sync:
       → file was edited post-sync, not registered → move to diverged
       → log warning
4. DIFF thezero@HEAD vs thezero@last_synced (template/ subdir):
   → list changed_files
   → list added_files
   → list removed_files
5. PROCESS changed_files:
   for each f in changed_files:
     if f in pristine:
       # safe auto-apply
       apply diff to client file
       update hash_at_sync
       log in sync_history.applied
     elif f in diverged:
       # 3-way merge required
       get base = thezero@last_synced:f
       get ours = client:f
       get theirs = thezero@HEAD:f
       if trivial (added lines only): auto-merge
       else:
         present to agent:
           "Conflict in {f}. Base ABC, ours XYZ, theirs DEF.
            Options: apply-theirs / keep-ours / merge-custom / skip"
         await decision
         apply
         log in sync_history.conflicts_resolved
6. PROCESS added_files:
   for each f in added_files:
     # not in either registry
     ask agent: "New file {f} from thezero. Include? (yes/no)"
     if yes:
       copy file
       add to pristine registry
     if no:
       add to skipped_during_sync
7. PROCESS removed_files:
   for each f in removed_files:
     # thezero removed, but klient may still have custom version
     if f in pristine:
       ask: "File {f} removed upstream. Delete yours? (yes/no/keep-forever)"
     if f in diverged:
       ask: "File {f} removed upstream but you customized. Keep yours? (yes/no)"
     log decision
8. UPDATE .thezero-sync.md:
   last_synced.commit = thezero@HEAD sha
   last_synced.date = now
   append sync_history entry
9. GIT COMMIT changes:
   branch: sync/thezero-<date>
   commit: "chore: sync thezero-template to <sha>"
   DON'T push (user review first)
10. REPORT:
    - X files auto-applied
    - Y conflicts resolved
    - Z files skipped
    - Next steps: review branch, merge when ready
```

### AC4 — Conflict resolution UX

- [ ] **AC4.1** Agent presents 3-way diff w readable format
- [ ] **AC4.2** Opcje: apply-theirs (use upstream), keep-ours (no change), merge-custom (open editor), skip (no decision — log as pending)
- [ ] **AC4.3** Auto-merge dla trivial cases (added lines no overlap) z notification
- [ ] **AC4.4** Tracking: ile conflicts na ten sync (metric w state)

### AC5 — Dry run mode

- [ ] **AC5.1** `--dry-run` produces report WITHOUT changing any file
- [ ] **AC5.2** Report format: markdown z sections "Auto-apply", "Conflicts (requires decision)", "Skipped (removed upstream)", "New files offered"

### AC6 — Rollback

- [ ] **AC6.1** Sync happens on new git branch `sync/thezero-<date>` — user może po prostu `git branch -D` jeśli chce abandonować
- [ ] **AC6.2** `.thezero-sync.md` state ZMIENIA się tylko po merge do main (via git hook albo --commit-final flag)

### AC7 — Error handling

- [ ] **AC7.1** Network fail clone → retry 3x
- [ ] **AC7.2** Conflict agent timeout → pauza, zapisuj progress, log pending
- [ ] **AC7.3** Git dirty state → abort z komunikatem "stash or commit first"
- [ ] **AC7.4** Malformed `.thezero-sync.md` → validate errors + guide fix

## Technical design

### Skill location

`~/.claude/plugins/cache/bitfloo-cbc/cbc/X.Y.Z/skills/thezero-sync/SKILL.md`

### Dependencies

- `git` (branch management, diff)
- `sha256sum` (file hashing)
- Agent interaction (for conflict resolution)
- YAML parser (state file)
- Optionally `diff3` tool (posix) for 3-way merge

### Corner cases

| Przypadek | Zachowanie |
|-----------|-----------|
| Klient zmienił pristine file bez update sync.md | Auto-detect przez hash, move to diverged, warn |
| Plik thezero renamed (add + delete) | Detect via similarity — ask "Apply rename?" |
| thezero removed file klient chce keep | Honor keep, remove from any registry, add `explicit_keep` list |
| Thezero sha nie istnieje (force push) | Fail; guide user "thezero history changed — manual recovery" |
| Merge nie kończy się w rozsądnym czasie (user nie odpowiada) | Save progress, exit, continue next run |

### Phase 3.1 — Convention commits integration

Po pierwszej iteracji może dodać:
- Parse thezero commits z last_synced → HEAD
- Group zmiany per commit message
- Present "N fixes available. Apply all?" z option to per-commit cherry-pick

## Verification

- [ ] Działa na fejkowym kliencie z `.thezero-sync.md` na v0.1.0 + thezero@v0.2.0 (z jakimiś zmianami w template)
- [ ] Respektuje diverged files (nie nadpisuje)
- [ ] Poprawnie auto-apply pristine files
- [ ] Conflict resolution działa (agent decision apply)
- [ ] State file aktualny po sync
- [ ] Rollback przez `git branch -D` nie psuje state

## Out of scope (Phase 3)

- Syncing z arbitrary thezero fork (tylko canonical `Bitfloo/thezero`)
- Backwards sync (klient → thezero) — to wymaga innego skilla
- Automatyczny merge code-reviewer agent (może w 3.1)
- Per-section semantic diff (kompozytor może widzieć że HeroSection ma inne props niż expect) — Phase 4

## Triggers dla implementacji

**Zacznij pisać skill gdy**:
- Mamy 2+ klientów z `.thezero-sync.md` state files
- Zrobiliśmy bug fix w `thezero/packages/template/` który chcemy propagować
- Manualny cherry-pick zabrał >30min (czas uzasadnia tooling)
