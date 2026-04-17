#!/usr/bin/env bash
# install.sh — rejestruje git hooks w .git/hooks/ per-developer machine.
#
# Uruchom raz po clone: ./scripts/hooks/install.sh

set -euo pipefail

REPO_ROOT="$(git rev-parse --show-toplevel)"
HOOKS_SRC="$REPO_ROOT/scripts/hooks"
HOOKS_DEST="$REPO_ROOT/.git/hooks"

for hook in commit-msg; do
  src="$HOOKS_SRC/$hook"
  dest="$HOOKS_DEST/$hook"

  [ -f "$src" ] || continue

  if [ -e "$dest" ] && [ ! -L "$dest" ]; then
    echo "  ⚠  $dest already exists (not a symlink) — backing up as $dest.bak"
    mv "$dest" "$dest.bak"
  fi

  ln -sf "../../scripts/hooks/$hook" "$dest"
  chmod +x "$src"
  echo "  ✓ $hook hook installed"
done

# Also enable commit template if not already
if ! git config --local commit.template >/dev/null 2>&1; then
  if [ -f "$REPO_ROOT/.gitmessage" ]; then
    git config --local commit.template .gitmessage
    echo "  ✓ commit.template = .gitmessage"
  fi
fi

echo ""
echo "Installed. Test: commit z bad message should fail:"
echo "  git commit --allow-empty -m 'bad message' "
echo ""
echo "Skip validation (emergency only):"
echo "  git commit --no-verify ..."
