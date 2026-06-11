---
paths: ["**"]
---

# Karpathy Coding Principles

Behavioral guidelines to reduce common LLM coding mistakes. Adopt for every CBC-builder agent, skill, hook, and slash-command implementation task. Merge with project-specific instructions as needed.

Source: [forrestchang/andrej-karpathy-skills](https://github.com/forrestchang/andrej-karpathy-skills) — distilled from Andrej Karpathy's observations on LLM coding pitfalls. Imported verbatim where possible; CBC-specific clarifications added only where they prevent ambiguity.

## The diagnostic (Karpathy, March 2025)

The four principles below answer this verbatim observation:

> "The models make wrong assumptions on your behalf and just run along with them without checking. They don't manage their confusion, don't seek clarifications, don't surface inconsistencies, don't present tradeoffs, don't push back when they should. They really like to overcomplicate code and APIs, bloat abstractions, don't clean up dead code... implement a bloated construction over 1000 lines when 100 would do. They still sometimes change/remove comments and code they don't sufficiently understand as side effects, even if orthogonal to the task."

This is the load-bearing reason every section below exists. Re-read it before claiming any rule "doesn't apply."

**Tradeoff:** These guidelines bias toward caution over speed. For trivial tasks, use judgment.

---

## 1. Think Before Coding

**Don't assume. Don't hide confusion. Surface tradeoffs.**

Before implementing:
- State your assumptions explicitly. If uncertain, ask.
- If multiple interpretations exist, present them — don't pick silently.
- If a simpler approach exists, say so. Push back when warranted.
- If something is unclear, stop. Name what's confusing. Ask.

### CBC application

- Before editing a hook, confirm which hook event it fires on (`SessionStart`, `UserPromptSubmit`, `PostToolUse`…) and which JSON fields the event carries — see `docs/specs/2026-04-11-cbc-dev-tooling-design.md` §2.
- Before adding a core module under `src/core/`, check whether an adjacent module already provides the capability (`entity`, `knowledge`, `access`, `config`…) and prefer extending over duplicating.
- If a task spec conflicts with `CLAUDE.md §Development rules`, stop and flag — do not silently pick a side.

---

## 2. Simplicity First

**Minimum code that solves the problem. Nothing speculative.**

- No features beyond what was asked.
- No abstractions for single-use code.
- No "flexibility" or "configurability" that wasn't requested.
- No error handling for impossible scenarios.
- If you write 200 lines and it could be 50, rewrite it.

Ask yourself: "Would a senior engineer say this is overcomplicated?" If yes, simplify.

### CBC application

- One logical change per commit (CLAUDE.md §9). If the diff spans unrelated concerns, split.
- Hooks: pipeline pattern (CLAUDE.md §5) — each step a testable function, hook is the coordinator. Resist building DI containers or strategy registries for two call-sites.
- Fixtures > factories. Tests live under `tests/fixtures/` when a real on-disk shape is needed; don't build fixture generators until a third consumer demands it.

---

## 3. Surgical Changes

**Touch only what you must. Clean up only your own mess.**

When editing existing code:
- Don't "improve" adjacent code, comments, or formatting.
- Don't refactor things that aren't broken.
- Match existing style, even if you'd do it differently.
- If you notice unrelated dead code, mention it — don't delete it.

When your changes create orphans:
- Remove imports/variables/functions that YOUR changes made unused.
- Don't remove pre-existing dead code unless asked.

The test: Every changed line should trace directly to the user's request.

### CBC application

- Zero client data (CLAUDE.md §3) — never edit fixtures outside `tests/fixtures/` even if they look stale.
- `dist/` is committed (CLAUDE.md §10). Rebuild it (`npm run build`) only for the modules you touched; do not re-run a formatter over unrelated bundles.
- `.cbc/` runtime artifacts belong to users' projects, not this repo. Never edit files under `.cbc/` as part of an implementation task.

---

## 4. Goal-Driven Execution

**Define success criteria. Loop until verified.**

Transform tasks into verifiable goals:
- "Add validation" → "Write tests for invalid inputs, then make them pass"
- "Fix the bug" → "Write a test that reproduces it, then make it pass"
- "Refactor X" → "Ensure tests pass before and after"

For multi-step tasks, state a brief plan:

```
1. [Step] → verify: [check]
2. [Step] → verify: [check]
3. [Step] → verify: [check]
```

Strong success criteria let you loop independently. Weak criteria ("make it work") require constant clarification.

### CBC application

- TDD always (CLAUDE.md §1) — failing test first, then implementation, then `npm run build && npx vitest run <file>` green.
- Before claiming done: run `npm run build && npm test && npm run typecheck` and quote actual output (pass count, exit code). "Should pass" is not evidence.
- Commit prefix signals the check: `feat:` → behavioral test exists, `fix:` → regression test reproduces the bug, `test:` → coverage-only (no logic change).

---

## Enforcement (which auditor catches what)

These principles are not honor-system. Each maps to an auditor dispatched by `cbc:push-gate` over a pre-push diff. If you violate a principle, the corresponding auditor flags it before the push lands.

| Principle | Auditor(s) | What they catch |
|---|---|---|
| 1. Think Before Coding | (no auditor — human review) | Silent assumptions, missing clarifications. Surfaced via TASK_BRIEF self-check (`rules/cbc-brief-schema.md` Pre-Emit Verification). |
| 2. Simplicity First | `cbc:kiss-auditor`, `cbc:overengineering-guard` | LOC/nesting/complexity thresholds; abstractions/config knobs/DI with < 2 consumers. |
| 3. Surgical Changes | `cbc:slop-scorer`, `cbc:overthinking-guard` | Verbose/sycophantic/dead comments, removed WHY-comments, drive-by refactor; branching against impossible states. |
| 4. Goal-Driven Execution | `cbc:task-verifier` (TDD enforcement) | Failing-test-first discipline; "should pass" without quoted output. |

SSOT: this table points; the auditors own their checks. Do not re-document auditor checks here — read the agent file directly.

---

## Why this rule file exists

These guidelines are working if:

- Fewer unnecessary changes appear in diffs.
- Fewer rewrites because work was overcomplicated.
- Clarifying questions come before implementation rather than after mistakes.

If you notice the opposite, the rule is being ignored — raise it to the user, don't silently continue.
