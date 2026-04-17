# Security Policy — bitfloo/webfloo

## Scope

`bitfloo/webfloo` jest proprietary private library. Repo dostępne tylko dla członków org `Bitfloo` + authorized consumers.

## Reporting a Vulnerability

**Nie otwieraj** publicznego issue. Reportuj na:

- Email: `security@bitfloo.com`
- Encrypted (preferred): PGP key at `https://bitfloo.com/.well-known/security.txt`

Response SLA:
- Acknowledgment: 24h
- Initial triage: 72h
- Patch release: 14 dni dla high/critical severity

## Supported Versions

Tylko **najnowsza stable minor** otrzymuje security patche. W 0.x (pre-stable): tylko HEAD.

Po 1.0:

| Version | Supported |
|---------|-----------|
| Latest minor | ✅ security + bug fixes |
| Poprzednia minor | ⚠️ security only przez 30 dni |
| Starsze | ❌ |

## Security practices

### Dla maintainers

- Branch protection: `main` wymaga PR review + passing CI
- PAT tokens nie-committed (ani w CI logs, ani w repo)
- Release workflow uses `GITHUB_TOKEN` (scoped per job, auto-rotation)
- `composer validate --strict` uruchamia się w CI (prevents malformed package metadata)

### Dla consumers (bitfloo-web, klienci)

- Używaj `"^0.1"` (albo `"^1.0"`) zamiast `"dev-main"` — fixed tagi są audit'owane
- Configure `composer.lock` commit w consumer — locks exactly checked version
- GitHub PAT dla `auth.json` scope minimal: `repo` (dla private vcs access)
- Renew PAT co 6 miesięcy (GitHub Settings → Personal access tokens)

### Threat model

Realistyczne zagrożenia (rozważone):
- **Supply chain**: release-please + GitHub Packages = każdy release podpisany commitem + signed tag (gdy enabled)
- **Dependency confusion**: composer `type: vcs` ściąga bezpośrednio z GitHub, NIE używa Packagist fallback
- **Rogue maintainer**: CODEOWNERS + branch protection wymagają review innego członka org

### Non-goals

- Auditing klientów projektów (każdy klient ma własną security posture)
- Supply chain dla dep transitive (np. Laravel deps) — relied upon upstream security
