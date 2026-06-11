---
paths: ["**"]
---

# CBC Brief Schema

Canonical output format for OODA-loop agents that produce structured decisions consumed by downstream agents: `brain`, `dev-loop`, `task-splitter`, `builder`, `agenter`, `planner`.

**Single source of truth.** Any agent emitting a brief MUST use these exact shapes. Do not paraphrase field names or reorder sections — downstream parsers depend on the literal headings.

---

## Four Decision Types

Every brief-emitting agent produces exactly ONE of:

| Decision | Purpose | Emitted by | Consumed by |
|---|---|---|---|
| `TASK_BRIEF` | Implementation spec for a new feature / module / change | brain, planner | task-splitter (multi-file) or builder (single-file) |
| `FIX_BRIEF` | Root-cause analysis + fix instructions for a failing test / bug | brain | builder |
| `PHASE_REPORT` | End-of-phase spec-compliance audit | brain | dev-loop (phase gate) |
| `ESCALATE` | Human decision needed — agent refuses to proceed autonomously | any brief-emitting agent | human |

---

## TASK_BRIEF

```markdown
## Brain Brief — Task {id}: {name}

### Decision: TASK_BRIEF

### Spec Requirements
{Exact quotes from spec, with section name references. Must be verbatim from a file actually Read this turn.}

### Context from Prior Task
{Optional. If this task continues work from a prior MT/task in the same phase, state: what the prior task produced, which API/file it exposed, and what this task depends on. Use "None" if this is a standalone task. Populated by dev-loop translator when chaining MTs.}

### Errata Overrides
{Any errata from the plan that override spec for this task. "None" if none apply.}

### Approaches Considered
{2-3 candidate approaches with 1-line rationale and explicit rejection reason for non-winners. Forces Opus to reason, not template-fill.
Keep the candidates genuinely DISTINCT (not two variants of one idea) by reaching for different lenses — e.g. inversion ("what would make this fail?") or constraint-relaxation ("drop an assumed-fixed constraint"); pick 1-2 scaled to loop latency, do not run a full brainstorm. Toolkit: `agents/brainstorm.md`.
Use `None considered — task is mechanical (single obvious implementation)` to skip — but append the one-line reason it is mechanical (e.g. `… — single call site, no branching, no new contract`); a bare skip with no reason is a first-idea tunnel in disguise.
REQUIRED when `trust_state` ∈ {yellow, red}: ≥2 candidates AND a strongest counter-argument to the chosen one — the single best case AGAINST your own pick (anti-sycophancy; if you cannot name one, your divergence was too narrow). Format:
- Approach A: {description} — REJECTED because {reason}
- Approach B: {description} — CHOSEN because {reason}
- Strongest counter-argument to Approach B: {one sentence}}

### Files to Create/Modify
- `src/core/{module}.ts` — {responsibility in one sentence}
- `tests/core/{module}.test.ts` — {what behaviors to test}

### Tests to Write First
1. `should {behavior}` — Input: {input}. Expected: {output/behavior}.
2. `should {behavior}` — Input: {input}. Expected: {output/behavior}.
3. `should handle {edge case}` — Input: {edge input}. Expected: {graceful behavior}.

### API Contracts to Respect
{List function signatures of existing modules this task imports from. Read from actual source files, not memory.}

### CBC Rules Checklist
- [ ] Pipeline pattern (NEW hooks only, aspirational): each step is an extractable pure function `(ctx) → ctx`, hook body is a coordinator. No `PipelineContext` type exists in code today — do NOT import or reference one. Existing hooks are imperative and are NOT to be retrofitted as a side effect of an unrelated change.
- [ ] Hook I/O: readFileSync(0) in, stdout.write(JSON.stringify()) out, exit(0) (if writing hook)
- [ ] Graceful degradation: try/catch → {continue: true} (if writing hook)
- [ ] CommonJS: no import.meta, no top-level await
- [ ] Single dep: js-yaml only (no new imports from npm)
- [ ] No client data in source or tests (fixtures only)

### Risks
- {risk}: {mitigation}

### What NOT to Do
- {specific anti-pattern for this task}
- Do NOT touch files outside src/, tests/, templates/
```

### Context-from-Prior usage

When dev-loop translates an MT into a TASK_BRIEF for Builder, the translator MUST populate "Context from Prior Task" with:

- The MT-id(s) this one depends on (from the Splitter's dependency graph)
- The exact symbols/files produced by those prior MTs that this MT imports or calls
- Any invariants established by prior MTs that this MT must preserve

If the MT has no dependencies (first MT in its parallel group), write `None`.

Rationale: without this field, Builder re-derives context by re-reading prior diffs, wasting tokens and risking drift. With it, Builder starts with a concrete handoff contract.

---

## FIX_BRIEF

```markdown
## Brain Brief — Fix: {short description}

### Decision: FIX_BRIEF

### Classification: {TEST BUG | CODE BUG | INTEGRATION BUG | ENV BUG | FLAKY | ORDER-DEPENDENT | SPEC BUG}

### Evidence
{Exact error message from test output}

### Prior Attempts
{Read via `tail -5 .cbc-dev/cycles.jsonl 2>/dev/null`. Closer writes free-text per-agent summaries (no structured `failed`/`reverted` markers), so derive outcome from context:
- `shipped` = cycle's `closer` field is `CLEAN` AND builder field doesn't describe rework
- `reverted` = subsequent cycle against same test/symbol with a different approach, OR explicit "reverted" wording
- `failed` = cycle followed by another FIX cycle against the same test/symbol within 2 cycles

Format each entry as:
- `{cycle-id}: APPROACH "{summary from that cycle's builder field}" → OUTCOME {shipped|failed|reverted|unknown}`

Use `None — first attempt` when `tail` returns no matching cycles (empty/missing file is OK — never fabricate entries).

REQUIRED when `retry_count ≥ 1`. The chosen Fix Approach below MUST differ from any prior APPROACH marked `failed`/`reverted` — reusing a rejected approach is an autonomous-escalation trigger. `unknown` outcomes are treated as `failed` for retry purposes (conservative default).

**What counts as a near-duplicate (mechanical test, not judgment):** two approaches are the SAME if they touch the **same symbol(s)/file(s)** AND share the **same fix class** (e.g. both "add a null/undefined guard", both "change the return type/shape", both "adjust the test's expected value", both "reorder/await an async call") — differing line numbers alone do NOT make them different. A genuinely new approach changes the symbol set OR the fix class, and you must state which in one sentence (e.g. "prior patched the guard in `entity.ts`; this changes the caller in `knowledge.ts`"). If you cannot articulate that difference in one sentence, it IS a near-duplicate → ESCALATE rather than re-run it.}

### Root Cause
{What is wrong and why, with file:line references}

### Fix Options Considered
{2-3 candidate fixes with tradeoffs (LOC, blast radius, risk).
Use `None — fix is unambiguous (single obvious patch)` to skip.
REQUIRED when `trust_state` ∈ {yellow, red} OR `retry_count ≥ 1`: ≥2 options AND a strongest counter-argument to the chosen one — the single best case AGAINST your own pick (anti-sycophancy). Format:
- Option A: {patch} — ~{LOC} lines, risk: {low|med|high} — REJECTED because {reason}
- Option B: {patch} — ~{LOC} lines, risk: {low|med|high} — CHOSEN because {reason}
- Strongest counter-argument to Option B: {one sentence}}

### Fix Approach
{Specific instructions for the CHOSEN option: which file, which lines, what to change}

### Verification
{What command to run and what output to expect after fix}
```

**Classification notes:**
- `ERRATA BUG` is a sub-case of `SPEC BUG` — classify as `SPEC BUG` and cite the conflicting errata in Evidence.
- `FLAKY` requires evidence of non-determinism (≥2 runs with divergent outcomes, or explicit timing/race quote). Do not default to FLAKY to dodge classification.
- `ORDER-DEPENDENT` requires the test passes via `vitest run <file>` but fails in the full suite — cite both outcomes in Evidence.

---

## PHASE_REPORT

```markdown
## Brain Phase Report — Phase {n}: {name}

### Decision: PHASE_REPORT

### Spec Compliance

| Requirement | Status | Evidence |
|---|---|---|
| {requirement from spec} | COMPLIANT / DRIFT / CONTRADICTION | {file:line or explanation} |
| ... | ... | ... |

### Summary
- Compliant: {N}/{total}
- Drift: {N} — {list}
- Contradictions: {N} — {list}

### Recommendation
{PROCEED to next phase | FIX {list} before proceeding}
```

---

## ESCALATE

```markdown
## Brain Escalation

### Decision: ESCALATE

### Reason
{Why this cannot be resolved autonomously}

### Context
{Phase, task, what was attempted, what failed}

### What Human Needs to Do
{Specific ask: clarify spec, make architecture decision, fix environment, etc.}
```

---

## Pre-Emit Verification (Mandatory)

Before emitting ANY brief, the agent runs this self-check. If any item fails, do NOT emit — fix, substitute a sentinel, or escalate.

1. **Spec quotes are verbatim.** Every string under "Spec Requirements" / "Evidence" must be copy-pasted from a file actually Read this turn, with `path:line` citation. If the verbatim quote cannot be located, write `QUOTE_NOT_FOUND: <what was looked for in {file}>` and STOP.
2. **API contracts came from src/.** Every function signature under "API Contracts to Respect" was read from an actual file in `src/` this turn, not recalled. If not Read, write `CONTRACT_NOT_VERIFIED: <module>` and STOP.
3. **Errata section is present (TASK_BRIEF only).** For TASK_BRIEF output, the "Errata Overrides" block exists and contains either real entries or the literal string `None` — never omitted, never blank. Not applicable to FIX_BRIEF / ESCALATE / PHASE_REPORT.
4. **Classification is evidence-backed (FIX only).** The chosen class has ≥1 quoted line of test output or code supporting it; otherwise ESCALATE.
5. **CBC Rules Checklist is grounded (TASK_BRIEF only).** The checklist items you include must trace to `rules/cbc-engineering-principles.md` — either Read this turn, or a rule you can quote verbatim. If neither, DROP the item rather than reciting from memory. Do NOT invent type names, constants, or APIs in the checklist (e.g. `PipelineContext` is a documentation shape, not a real type — do not tell Builder to import it).
6. **Terminal output gate.** The brief ends with the full template block OR one of the sentinels (`NEEDS_INPUT`, `QUOTE_NOT_FOUND`, `CONTRACT_NOT_VERIFIED`, `INVESTIGATION_CHECKPOINT`, `ESCALATE`). The agent must not stop mid-thought — there is no resume. If you run out of work budget before verification passes, emit `INVESTIGATION_CHECKPOINT` per Brain's Investigation Budget section.
7. **Builder-simulation gate (semantic, not syntactic).** Role-play builder for ~60s reading ONLY this brief, and walk this REQUIRED ambiguity checklist — categories to actively check against the brief, not illustrative examples to skim:
   - (a) **error contract** — which error type/shape, thrown on which inputs
   - (b) **sync vs async** — does the API return a value or a Promise
   - (c) **new vs extend** — create a new file/function or extend a named existing one
   - (d) **validation strictness** — reject or coerce/default on bad input
   - (e) **empty/edge inputs** — defined behavior for empty/null/boundary

   Name the FIRST checklist item the brief leaves unresolved. If any is unresolved by Spec Requirements / API Contracts / an explicit constraint, add the clarification or ESCALATE. Only after walking all five with none unresolved may you self-attest at the end of the brief with `BUILDER_SIM: no ambiguity detected` — appending that line without having walked the checklist is a protocol violation. This check is mandatory for TASK_BRIEF and FIX_BRIEF; skip for PHASE_REPORT and ESCALATE.

The sentinels (`NEEDS_INPUT`, `QUOTE_NOT_FOUND`, `CONTRACT_NOT_VERIFIED`, `INVESTIGATION_CHECKPOINT`) are valid terminal outputs — they are how an agent refuses to hallucinate.

---

## MT → TASK_BRIEF Translation (dev-loop)

When dev-loop translates a micro-task (from task-splitter) into a TASK_BRIEF for Builder, it fills the template above with:

| TASK_BRIEF field | Source in MT block |
|---|---|
| Spec Requirements | MT's `Scope` field |
| Context from Prior Task | MTs this one `Depends on` + their produced symbols |
| Errata Overrides | `None (managed by Brain's original brief)` |
| Approaches Considered | `None considered — task is mechanical (single obvious implementation)` unless `trust_state ≠ green`, in which case translator inherits Brain's original `Approaches Considered` block |
| Files to Create/Modify | MT's `File` + `Test` fields |
| Tests to Write First | MT's `Tests first` list |
| API Contracts to Respect | Brain's original TASK_BRIEF, filtered to this MT's imports |
| CBC Rules Checklist | MT's `CBC rules` field |
| Risks | `LOC budget: {MT's LOC estimate}` |
| What NOT to Do | `Do NOT touch files outside this MT's scope` |

---

## Peer Specialist Contracts (orchestrator delegation schema)

Every user-facing peer specialist declares three fields in its frontmatter (or is added to the table below as SSOT until frontmatter is migrated). Orchestrator performs schema-alignment check before `DELEGATE → cbc:{agent}`: target's `accepts:` must cover the planned input; chain `A → B` is valid iff `A.returns ⊆ B.accepts`.

**Shared schemas** (vocabulary used in accepts/returns):

- `EntityScopedRequest` — `{entitySlug, taskType, goal, constraints?}` (baseline input for all entity-aware agents)
- `DraftedContent` — `{text, entity, taskType, sources[], blacklist_check}`
- `RawContent` — `{text, entity?, contentType}` (unverified, pre-draft)
- `ReviewVerdict` — `{verdict: PASS|REVISE|REJECT, findings[], quotes[]}`
- `TaskPlan` — `{ordered_tasks[], dependencies[], estimates?}`
- `StrategyOptions` — `{options[], ranking, recommendation, decision_log}`
- `ProjectStateReport` — `{milestones, risks, blockers, next_actions}`
- `OnboardingBrief` — `{identity, conventions, recent_decisions, read_next[]}`
- `RecapReport` — `{range, events[], git_summary, memory_delta}`
- `FactCapture` — `{kind: fact|decision|preference|event, text, entity, confidence}`
- `KnowledgeBundle` — `{loaded_paths[], denied[], merged_snippets[]}`
- `OpsResult` — `{action, status, artifacts[]}` (server ops, api calls, PRs)
- `AuditReport` — `{score, findings[], verdict: PASS|FAIL}`

**Peer specialist contracts (SSOT table)**:

| Agent | accepts | returns | needs (default) |
|---|---|---|---|
| drafter | EntityScopedRequest | DraftedContent | `_knowledge/brand.md`, `_knowledge/voice.md`, `_knowledge/offer.md`, `_memory/compiled/*` |
| reviewer | DraftedContent \| RawContent | ReviewVerdict | `_knowledge/brand.md`, `_knowledge/voice.md`, `_knowledge/conventions.md`, `access.yaml` |
| context-hunter | EntityScopedRequest | KnowledgeBundle | `_knowledge/*`, `_memory/compiled/*`, `_memory/sessions/*`, `entity-index.json` |
| note-keeper | FactCapture | FactCapture (persisted) | `_memory/sessions/{entity}/`, `_memory/proposals/` |
| recap | EntityScopedRequest | RecapReport | `.cbc/event-logs/`, `_memory/sessions/*`, `_memory/compiled/*`, git log |
| tasker | EntityScopedRequest | TaskPlan | `_knowledge/*`, `_memory/compiled/*` (reuse patterns) |
| planner | EntityScopedRequest | StrategyOptions | `_knowledge/profile.md`, `_knowledge/goals.md`, `_memory/compiled/*` (past decisions) |
| pm | EntityScopedRequest | ProjectStateReport | `projects/{slug}/pm/milestones.md`, `risks.md`, `tasks.md`, `log.md` |
| onboarding-guide | EntityScopedRequest | OnboardingBrief | `_knowledge/*`, `_memory/compiled/*`, entity bridge, `access.yaml` |
| agenter | EntityScopedRequest | OpsResult (new agent file) | existing `agents/*.md` as reference corpus |
| github-ops | EntityScopedRequest | OpsResult | repo access (gh CLI) |
| devops-linux | EntityScopedRequest | OpsResult | ssh target, server inventory |
| ggl-sheet-api-master | EntityScopedRequest | OpsResult | sheets credentials |
| croner | EntityScopedRequest | OpsResult | host scheduling capability |
| security-auditor | EntityScopedRequest | AuditReport | repo tree, package manifests, git history |
| relation-guard | EntityScopedRequest | AuditReport (+ OpsResult if auto-fix) | all `.md` with `related:` frontmatter |
| orchestrator | user-prompt | DELEGATE \| HANDLE | `.cbc/config.yaml`, `entity-index.json`, `access.yaml`, task_routing |

**Atomicity invariant**: `accepts:` is a single schema or short union (≤3). `returns:` is a single schema. If an agent violates this, split it.

**Chain examples** (schema-aligned, valid):
- user → orchestrator → drafter → reviewer (DraftedContent ⊆ reviewer.accepts) ✓
- user → orchestrator → tasker → (per-task) drafter (each TaskPlan.ordered_tasks[i] → EntityScopedRequest) ✓
- user → orchestrator → planner → tasker (StrategyOptions.recommendation → EntityScopedRequest) ✓
- user → orchestrator → context-hunter → drafter (KnowledgeBundle enriches drafter's `needs:` pool) ✓

**Internal-only agents** (dev-loop/crystal-loop/push-gate coordinators dispatch these — NOT orchestrator): brain, builder, scout, closer, task-splitter, task-verifier, code-guardian, autodev, crystal-*, reviewer-fact-checker, reviewer-policy-guard, kiss-auditor, slop-scorer, ssot-auditor, prompt-analyzer, prompt-optimizer, loop-architect, bootstrap-validator, session-compiler.

---

## Provenance

Extracted from `agents/brain.md:130-231` and `agents/dev-loop.md:120-151` on 2026-04-18 per research sweep `docs/research/cc-agents-base-2026.md` (Dean Grover lift, 2026-04-01). Previous duplication between brain.md and dev-loop.md is now a pointer; this file is SSOT.

New field vs. pre-extraction shape: **Context from Prior Task** (TASK_BRIEF) — addresses MT chaining context loss.
