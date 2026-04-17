# scripts/

## `finalize-ecosystem.sh`

One-shot ecosystem Phase 1 completion — pushy, tags, auth, CI secret, PR.

### Prerequisites

1. **gh CLI authenticated**:
   ```bash
   brew install gh
   gh auth login
   ```
2. **GitHub PAT z scope**: `repo` + `read:packages` + `write:packages`
   - Generate: <https://github.com/settings/tokens> (Personal access tokens → classic)
   - Zapisz wartość na chwilę

### Usage

```bash
# Opcja A: token jako env
export BITFLOO_ECOSYSTEM_TOKEN=ghp_xxxxx
~/DEV/webfloo/scripts/finalize-ecosystem.sh

# Opcja B: prompt (input hidden)
~/DEV/webfloo/scripts/finalize-ecosystem.sh
```

### Co robi (9 kroków auto, 1 manualny)

1. Push webfloo main + tag `v0.1.0` (usuwa starego `v1.0.0` gdy istnieje)
2. Push thezero main + tag `core-v0.1.0` (publish.yml fire'uje na tag)
3. Enable GitHub Template Repository setting na `Bitfloo/thezero` (`gh api PATCH`)
4. Setup `~/.composer/auth.json` + `~/.npmrc` (auth lokalny)
5. `gh secret set GH_PACKAGES_TOKEN` w `Bitfloo/bitfloocom-web`
6. Poll aż `@bitfloo/thezero-core@0.1.0` pojawi się w GitHub Packages (max 3 min)
7. Push bitfloo-web consumer branch + create PR (`gh pr create`)
8. Watch PR CI (`gh pr checks --watch`)
9. Wyświetl instrukcje merge'u + post-merge verification

**Ostatni krok (merge) jest MANUALNY** — skrypt nie mergeuje za Ciebie (świadome gate przed main update).

### Idempotentność

Skrypt można uruchomić wielokrotnie — pomija kroki które są już zrobione (tagi, secrets, PR).

### Rollback

Jeśli coś pójdzie źle, każdy krok można cofnąć:

```bash
# Rollback tag
git tag -d v0.1.0 && git push origin :refs/tags/v0.1.0

# Rollback template setting
gh api -X PATCH repos/Bitfloo/thezero -f is_template=false

# Rollback secret
gh secret delete GH_PACKAGES_TOKEN --repo Bitfloo/bitfloocom-web

# Rollback PR
gh pr close feat/ecosystem-phase-1-consumer --delete-branch

# Rollback auth (manually edit files)
vim ~/.composer/auth.json ~/.npmrc
```
