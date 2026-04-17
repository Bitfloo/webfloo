#!/usr/bin/env bash
# finalize-ecosystem.sh — one-shot ecosystem Phase 1 completion.
#
# Uruchom z dowolnego katalogu. Wymaga:
#   - gh CLI authenticated (gh auth login)
#   - GitHub PAT z scope: repo + read:packages + write:packages
#     → zapisany jako env var BITFLOO_ECOSYSTEM_TOKEN albo prompt
#
# Idempotentne — bezpieczne re-running.

set -euo pipefail

WEBFLOO="$HOME/DEV/webfloo"
THEZERO="$HOME/DEV/thezero"
BITFLOO_WEB="$HOME/DEV/bitfloo-web"

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

log() { echo -e "${GREEN}[$(date +%H:%M:%S)]${NC} $*"; }
warn() { echo -e "${YELLOW}[WARN]${NC} $*"; }
err() { echo -e "${RED}[ERR]${NC} $*" >&2; exit 1; }

# --- Pre-flight ---

command -v gh >/dev/null 2>&1 || err "gh CLI not found. Install: brew install gh"
gh auth status >/dev/null 2>&1 || err "gh not authenticated. Run: gh auth login"

if [ -z "${BITFLOO_ECOSYSTEM_TOKEN:-}" ]; then
  echo -n "BITFLOO_ECOSYSTEM_TOKEN (PAT, scope: repo + read:packages + write:packages, input hidden): "
  read -rs BITFLOO_ECOSYSTEM_TOKEN
  echo
  [ -z "$BITFLOO_ECOSYSTEM_TOKEN" ] && err "Token required"
fi

# --- Step 1: Push webfloo main + tag v0.1.0 ---

log "Step 1/9: webfloo main + tag v0.1.0"
cd "$WEBFLOO"

if git rev-parse v1.0.0 >/dev/null 2>&1; then
  log "  removing stale local v1.0.0 tag"
  git tag -d v1.0.0 || true
fi

if git ls-remote --tags origin | grep -q "refs/tags/v1.0.0$"; then
  log "  removing v1.0.0 from remote (destructive, zero consumers confirmed)"
  git push origin :refs/tags/v1.0.0 || true
fi

git push origin main
if ! git rev-parse v0.1.0 >/dev/null 2>&1; then
  git tag -a v0.1.0 -m "webfloo v0.1.0 — first pre-stable release

Bootstrap release for GitHub Packages Composer vcs consumption (ADR-011).
From v0.2.0 onwards, release-please auto-manages version bumps based on
Conventional Commits."
  git push origin v0.1.0
else
  log "  v0.1.0 already exists locally, skipping tag create"
  git push origin v0.1.0 || true
fi

log "  webfloo @ v0.1.0 DONE"

# --- Step 2: Push thezero main + tag core-v0.1.0 ---

log "Step 2/9: thezero main + tag core-v0.1.0 (triggers publish workflow)"
cd "$THEZERO"

git push origin main
if ! git rev-parse core-v0.1.0 >/dev/null 2>&1; then
  git tag -a core-v0.1.0 -m "@bitfloo/thezero-core v0.1.0 — first pre-stable release

Bootstrap release publishing @bitfloo/thezero-core to GitHub Packages npm.
From core-v0.2.0 onwards, release-please auto-manages version bumps."
  git push origin core-v0.1.0
else
  log "  core-v0.1.0 exists, skipping"
  git push origin core-v0.1.0 || true
fi

log "  core-v0.1.0 pushed; publish.yml firing on tag — watch https://github.com/Bitfloo/thezero/actions"

# --- Step 3: Enable Template Repository setting ---

log "Step 3/9: Enable GitHub Template Repository for Bitfloo/thezero"
if gh api repos/Bitfloo/thezero --jq '.is_template' | grep -q true; then
  log "  already enabled, skipping"
else
  gh api -X PATCH repos/Bitfloo/thezero -f is_template=true >/dev/null
  log "  template repo enabled"
fi

# --- Step 4: Setup local auth files ---

log "Step 4/9: Setup ~/.composer/auth.json + ~/.npmrc"

mkdir -p "$HOME/.composer"
COMPOSER_AUTH_FILE="$HOME/.composer/auth.json"
if [ ! -f "$COMPOSER_AUTH_FILE" ] || ! grep -q "github-oauth" "$COMPOSER_AUTH_FILE"; then
  cat > "$COMPOSER_AUTH_FILE" <<JSON
{
  "github-oauth": {
    "github.com": "$BITFLOO_ECOSYSTEM_TOKEN"
  }
}
JSON
  chmod 600 "$COMPOSER_AUTH_FILE"
  log "  ~/.composer/auth.json created (chmod 600)"
else
  log "  ~/.composer/auth.json already has github-oauth entry"
fi

NPMRC="$HOME/.npmrc"
if ! grep -q "@bitfloo:registry" "$NPMRC" 2>/dev/null; then
  cat >> "$NPMRC" <<EOF

# BitFloo GitHub Packages (Phase 1 ecosystem)
@bitfloo:registry=https://npm.pkg.github.com
//npm.pkg.github.com/:_authToken=$BITFLOO_ECOSYSTEM_TOKEN
EOF
  log "  ~/.npmrc appended @bitfloo registry + token"
else
  log "  ~/.npmrc already has @bitfloo config"
fi

# --- Step 5: Add GH_PACKAGES_TOKEN secret to bitfloo-web ---

log "Step 5/9: Add GH_PACKAGES_TOKEN secret to Bitfloo/bitfloocom-web"
echo "$BITFLOO_ECOSYSTEM_TOKEN" | gh secret set GH_PACKAGES_TOKEN \
  --repo Bitfloo/bitfloocom-web >/dev/null
log "  secret set"

# --- Step 6: Wait for @bitfloo/thezero-core@0.1.0 to publish ---

log "Step 6/9: Wait for @bitfloo/thezero-core@0.1.0 GitHub Packages publish"
MAX_WAIT=180  # 3 min
ELAPSED=0
while [ $ELAPSED -lt $MAX_WAIT ]; do
  if gh api orgs/Bitfloo/packages/npm/thezero-core/versions --jq '.[].name' 2>/dev/null | grep -q "^0.1.0$"; then
    log "  @bitfloo/thezero-core@0.1.0 published"
    break
  fi
  log "  waiting for publish workflow to finish... (${ELAPSED}s)"
  sleep 10
  ELAPSED=$((ELAPSED + 10))
done

if [ $ELAPSED -ge $MAX_WAIT ]; then
  warn "Timeout waiting for publish. Check manually: https://github.com/Bitfloo/thezero/actions"
  warn "Continuing — you may need to re-run composer update manually later"
fi

# --- Step 7: Push bitfloo-web consumer branch ---

log "Step 7/9: Push bitfloo-web consumer branch + create PR"
cd "$BITFLOO_WEB"

if ! git ls-remote origin feat/ecosystem-phase-1-consumer | grep -q feat/ecosystem-phase-1-consumer; then
  git push -u origin feat/ecosystem-phase-1-consumer
else
  log "  branch already exists on remote"
fi

# Check if PR already exists
PR_NUMBER=$(gh pr list --head feat/ecosystem-phase-1-consumer --json number --jq '.[0].number' 2>/dev/null || echo "")
if [ -z "$PR_NUMBER" ]; then
  PR_URL=$(gh pr create \
    --title "Phase 1: consume ecosystem via registries (webfloo vcs + thezero-core npm)" \
    --body "Per docs/plans/2026-04-17-ecosystem-phase-1.md (w Bitfloo/webfloo).

Changes:
- composer.json: type path → type vcs, ^1.0 → ^0.1
- package.json: @bitfloo/thezero → @bitfloo/thezero-core
- vite.config.js: cleanup packageThemes
- CLAUDE.md: consumer status, release workflow, auth setup docs
- .github/workflows/check.yml: CI z COMPOSER_AUTH + NODE_AUTH_TOKEN

Prerequisites verified:
- ✅ webfloo v0.1.0 on remote
- ✅ @bitfloo/thezero-core@0.1.0 on GitHub Packages
- ✅ GH_PACKAGES_TOKEN secret configured

Post-merge action: forked template w resources/js/themes/bitfloo/ pozostaje; gradual migracja imports na @bitfloo/thezero-core/* w follow-up PR-ach.")
  log "  PR created: $PR_URL"
else
  log "  PR #$PR_NUMBER already exists"
  gh pr view "$PR_NUMBER" --web >/dev/null 2>&1 || true
fi

# --- Step 8: Wait for PR CI to complete ---

log "Step 8/9: Waiting for PR CI run to finish"
gh pr checks feat/ecosystem-phase-1-consumer --watch 2>&1 | tail -10 || \
  warn "CI check command failed — verify manually in GitHub UI"

# --- Step 9: Offer to merge PR ---

log "Step 9/9: PR ready to merge"
echo ""
echo "=============================================================="
echo "  All automated steps DONE. Manual gate remaining:"
echo ""
echo "  Review PR w GitHub UI: $(gh pr view --json url --jq .url)"
echo ""
echo "  Po review merge:"
echo "    gh pr merge feat/ecosystem-phase-1-consumer --squash --delete-branch"
echo ""
echo "  Post-merge verification:"
echo "    cd $BITFLOO_WEB"
echo "    git pull origin main"
echo "    composer update bitfloo/webfloo"
echo "    pnpm install --frozen-lockfile  (lub npm install)"
echo "    make check"
echo "    npm run build:bitfloo"
echo ""
echo "  Ecosystem status:"
echo "    webfloo @ v0.1.0:       https://github.com/Bitfloo/webfloo/releases/tag/v0.1.0"
echo "    thezero-core @ 0.1.0:   https://github.com/Bitfloo/thezero/packages"
echo "    bitfloo-web PR:         $(gh pr view --json url --jq .url 2>/dev/null || echo 'see GitHub')"
echo "=============================================================="
