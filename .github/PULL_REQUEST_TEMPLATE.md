## Summary

<!-- 1-3 zdań: co i dlaczego -->

## Conventional Commit

- [ ] Title: `<type>(<scope>): <description>` — uruchamia release-please bump
- [ ] Type ∈ {feat, fix, docs, chore, refactor, test, ci, style, perf}
- [ ] Breaking? `!` w type albo `BREAKING CHANGE:` w body (UWAGA: 0.x → minor; 1.x+ → major)

## Type of change

- [ ] `feat` — nowy Model / Resource / Service / Trait
- [ ] `fix` — bug fix
- [ ] `docs` — ADR, plan, CLAUDE.md, README
- [ ] `chore` — deps, workflow, infra
- [ ] `refactor` — internal reorganizacja bez behavior change
- [ ] `test` — nowe / updated testy

## Impact na konsumentów

- [ ] Żadne repo nie wymaga action po merge
- [ ] Consumer musi `composer update bitfloo/webfloo` po release
- [ ] Consumer musi `php artisan migrate` (nowa migration)
- [ ] Consumer musi update config / .env (dokumentowane w release notes)

## Test plan

- [ ] `make check` green (pint + phpstan + phpunit)
- [ ] Jeśli nowy Model / Resource: testy w `tests/` dodane
- [ ] Jeśli breaking: ADR-update albo nowe ADR

## Cross-repo

- [ ] bitfloo-web nie wymaga zmian
- [ ] bitfloo-web potrzebuje follow-up PR (linka tutaj): #
- [ ] Update ADR? Link: docs/decisions/NNN-...md

## Checklist

- [ ] CI green
- [ ] CODEOWNERS: required reviewer assigned
- [ ] Docs updated (CHANGELOG auto-gen przez release-please)
